<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\sorm\Commutator;
use app\models\sorm\Operator;
use app\models\TrunkLoadLimit;
use app\models\voip\Pricelist;
use app\models\billing\ServiceTrunk;
use app\models\Trunk;
use app\models\sorm\Trunk as TrunkSorm;
use app\models\TrunkABfiltersRule;
use app\models\TrunkNumberPreprocessing;
use app\models\TrunkPriority;
use app\models\TrunkTrunkRule;
use yii\base\Exception;
use Yii;
use yii\db\Query;
use yii\db\StaleObjectException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\db\Expression;

class TrunkController extends JsonController
{
    
    const TYPE_ORIGINATION = 1;
    const TYPE_TERMINATION = 2;

    /**
     * @return \app\models\Trunk[]
     * @throws HttpException
     */
    public function actionList()
    {
        if (!\Yii::$app->user->can('trunk_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);

        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;

        return
            Trunk::find()
                ->select(['id', 'name', 'trunk_name'])
                ->where("( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id)
                ->orderBy('name')
                ->asArray()
                ->all();
    }
    
    /**
     * @return \app\models\Trunk[]
     * @throws HttpException
     */
    public function actionListWithContract()
    {
        if (!\Yii::$app->user->can('trunk_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);
        
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;
        
        return
            Trunk::find()
                ->select(['trunk.id', 'trunk.name', 'trunk.trunk_name'])
                ->innerJoin('billing.service_trunk st', 'st.trunk_id = auth.trunk.id')
                ->where("( trunk.server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or trunk.server_id = ".$server->id)
                ->andWhere('auth.trunk.our_trunk = false')
                ->andWhere('st.activation_dt < now()')
                ->andWhere('st.expire_dt > now()')
                ->andWhere('st.term_enabled = true')
                ->orderBy('name')
                ->asArray()
                ->all();
    }
    
    /**
     * @return \app\models\Trunk[]
     * @throws HttpException
     */
    public function actionListRoaming()
    {
        if (!\Yii::$app->user->can('trunk_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        if (empty($this->request['servers'])) {
            return [];
        }
    
        $servers = [];
        $hubs = [];
        
        foreach ($this->request['servers'] as $server) {
            $server = $this->getServerOr404($server['id']);
    
            $servers[] = $server['id'];
            $hubs[] = $server->hub_id > 0 ? $server->hub_id : 0;
        }
        
        return
            Trunk::find()
                ->select(['id', 'name', 'trunk_name'])
                ->where("(server_id in (select id from public.server where hub_id in (" . implode(',', $hubs) . ")) and sw_shared) or server_id in (" . implode(',', $servers) . ")")
                ->andWhere('roaming_orig = true or roaming_term = true')
                ->orderBy('name')
                ->asArray()
                ->all();
    }
    
    /**
     * @return \app\models\Trunk[]
     * @throws HttpException
     */
    public function actionListNameAndAlias()
    {
        if (!\Yii::$app->user->can('trunk_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $hub_id = $this->request['hub_id'];
        
        $query1 = Trunk::find()
                ->select(['id' => 'trunk_name', 'name' => 'trunk_name'])
                ->where("server_id in (select id from public.server where hub_id = :hub_id)")
                ->andWhere("trunk_name is not null")
                ->andWhere("trunk_name <> ''");
        
        $query2 = Trunk::find()
            ->select(['id' => 'trunk_name_alias', 'name' => 'trunk_name_alias'])
            ->where("server_id in (select id from public.server where hub_id = :hub_id)")
            ->andWhere("trunk_name_alias is not null")
            ->andWhere("trunk_name_alias <> ''");
        
        return $query1
            ->union($query2)
            ->orderBy('name')
            ->addParams([':hub_id' => $hub_id])
            ->asArray()
            ->all();
    }

    /**
     * @return \app\models\Trunk[]
     * @throws HttpException
     */
    public function actionRead()
    {
        if (!\Yii::$app->user->can('trunk_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);

        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;

        return
            Trunk::find()
                ->select([
                    'auth.trunk.id', 'auth.trunk.name', 'trunk_name', 'trunk_name_alias',
                    'default_priority', 'source_rule_default_allowed', 'destination_rule_default_allowed',
                    'auto_routing', 'our_trunk', 'auth_by_number', 'orig_redirect_number_7800', 'orig_redirect_number',
                    'term_redirect_number', 'show_in_stat', 'route_table_id', 'auth.trunk.server_id', 'capacity','sw_shared',
                    'road_to_regions', 'load_warning', 'orig_enabled', 'term_enabled', 'tech_trunk', 'pstn_trunk',
                    'mgmn_trunk', 'mgmn_orig_trunk', 'le8accept', 'mgmn2_orig', 'mgmn2_term', 'object_comment',
                    new Expression('CASE WHEN bb.id is null THEN false ELSE true END as is_blacklisted')
                ])
                ->with('routeTable')
                ->joinWith('trunkOrigTerm')
                ->leftJoin(
                    '(select trunk.id from auth.trunk trunk
                        join public.server pst on pst.id = trunk.server_id
                        join billing.blacklist bb on bb.type = \'trunk\' and bb.item = trunk.trunk_name and bb.server_id = trunk.server_id
                        union
                        select trunk.id from auth.trunk trunk
                        join public.server pst on pst.id = trunk.server_id
                        join billing.blacklist bb on bb.type = \'trunk\' and bb.item = trunk.trunk_name
                        join public.server psb on psb.id = bb.server_id
                        where psb.hub_id = pst.hub_id
                        and trunk.sw_shared
                    ) as bb',
                    'bb.id = trunk.id'
                )
                ->where("(auth.trunk.server_id in (select id from public.server where hub_id = ".$hub_id.") and sw_shared) or auth.trunk.server_id = ".$server->id)
                ->orderBy('trunk_name')
                ->asArray()
                ->all();
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionGet()
    {
        if (!\Yii::$app->user->can('trunk_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $regionId = $this->request['region_id'];
        
        $item =
            Trunk::find()
                ->with(['priorities', 'trunkRules', 'numberPreprocessing', 'numbersRules', 'usagesInMarketplace'])
                ->with([
                    'trunkSorm' => function($query) use ($regionId) {
                        $query->where(['region_id' => $regionId]);
                    },
                ])
                ->with('loadLimit')
                ->where(['id' => $this->request['id']])
                ->asArray()
                ->one();
        if ($item === null) {
            throw new HttpException(404, 'Транк не найден');
        }
        
        $item['default_auto_routing'] = $item['auto_routing'];
        $item['do_sync'] = false;
        
        foreach ($item['loadLimit'] as &$limit) {
            if (isset($limit['limit_absolute'])) {
                $limit['limit_mode'] = TrunkLoadLimit::LOAD_LIMIT_TYPE_ABSOLUTE;
                $limit['limit_value'] = $limit['limit_absolute'];
            } elseif (isset($limit['limit_relative'])) {
                $limit['limit_mode'] = TrunkLoadLimit::LOAD_LIMIT_TYPE_RELATIVE;
                $limit['limit_value'] = $limit['limit_relative'];
            } else {
                $limit['limit_mode'] = '';
                $limit['limit_value'] = '';
            }
        }

        return $item;
    }
    
    private function toggleSorm($trunk, $data, $regionId)
    {
        if (!$data['enabled']) {
            TrunkSorm::deleteAll(['code_trunk' => $trunk->id, 'region_id' => $regionId]);
        } else {
            if ($data['ip_addr'] && !filter_var($data['ip_addr'], FILTER_VALIDATE_IP)) {
                throw new Exception('IP address is incorrect');
            }
            
            $idsToStay = [];
    
            foreach ($data['items'] as $item) {
                if (isset($item['id'])) {
                    $idsToStay[] = $item['id'];
                }
            }
    
            TrunkSorm::deleteAll(['AND', 'code_trunk = :code_trunk AND region_id = :region_id', ['NOT IN', 'id', $idsToStay]], [':code_trunk' => $trunk->id, ':region_id' => $regionId]);
    
            foreach ($data['items'] as $item) {
                $this->processSormData($trunk, $item['old_name'], $data['name'], $data['ip_addr'], $item['is_show'], $data['groups'],
                    $data['sorm_operator_id'], $data['source_type_id'], $regionId, isset($item['object_comment']) ? $item['object_comment'] : null, isset($item['id']) ? $item['id'] : null);
            }
        }
    }
    
    private function processSormData($trunk, $oldName, $name, $ipAddr, $isShow, $groups, $sormOperatorId, $sourceTypeId, $regionId, $objectComment = null, $id = null)
    {
        if (!is_null($id)) {
            $trunkSorm = TrunkSorm::find()
                ->where(['code_trunk' => $trunk->id, 'id' => $id])
                ->one();
        } else {
            $trunkSorm = false;
        }
    
        if ($trunkSorm) {
            //edit
            $trunkSorm->name = $name;
            $trunkSorm->ip_addr = $ipAddr ? $ipAddr : null;
            $trunkSorm->is_show = isset($isShow) ? $isShow : false;
            $trunkSorm->groups = $groups ? '{' . implode(',', $groups) . '}' : '{}';
            $trunkSorm->old_name = $oldName;
            $trunkSorm->source_type_id = $sourceTypeId;
            $trunkSorm->object_comment = $objectComment;
    
            $trunkSorm->save();
        } else {
            //create
            if ($sormOperatorId == 1) {
                $operator = Operator::find()
                    ->with('commutator')
                    ->where(['server_id' => $trunk->server_id])
                    ->one();
            } else {
                $operator = null;
            }
    
            $dataToCreate = [
                'operator_id' => $operator ? $operator->id : '',
                'code_trunk' => $trunk->id,
                'ats_mnemo_code' => $operator ? $operator->commutator->comutator_str_id : 'reg' . $regionId,
                'type' => 2,
                'start_date' => date('Y-m-d H:i:s'),
                'name' => $name,
                'ip_addr' => $ipAddr ? $ipAddr : null,
                'old_name' => $oldName,
                'is_show' => isset($isShow) ? $isShow : false,
                'groups' => $groups ? '{' . implode(',', $groups) . '}' : '{}',
                'region_id' => $regionId,
                'sorm_operator_id' => $sormOperatorId,
                'source_type_id' => $sourceTypeId,
                'object_comment' => $objectComment
            ];
    
            $trunkSorm = TrunkSorm::create($dataToCreate);
            $trunkSorm->save();
        }
    }

    /**
     * @return \app\models\billing\ServiceTrunk[]
     */
    public function actionGetServiceTrunks()
    {
        if (!\Yii::$app->user->can('trunk_edit') && !\Yii::$app->user->can('trunk_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return isset($this->request['trunk_id']) && (int)$this->request['trunk_id'] ?
            ServiceTrunk::findActualByTrunkId($this->request['trunk_id']) :
            [];
    }

    /**
     * @throws FormValidationException
     * @throws HttpException
     * @throws \yii\db\Exception
     */
    public function actionSave()
    {
        if (!\Yii::$app->user->can('trunk_edit') && !\Yii::$app->user->can('trunk_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('trunk_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $trunk = $this->getTrunkOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('trunk_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            $trunk = Trunk::create($server);
        }

        $trunk->load($this->request, '');

        $transaction = Trunk::getDb()->beginTransaction();
        try {
            if ($trunk->isAttributeChanged('need_recalc_routing_report')) {
                $server->need_recalc_routing_report = true;
                if (!$server->save()) {
                    throw new FormValidationException($server);
                }
            }
    
            if (isset($this->request['sorm'])) {
                $this->toggleSorm($trunk, $this->request['sorm'], $this->request['region_id']);
            }

            if (!$trunk->save()) {
                throw new FormValidationException($trunk);
            }

            TrunkPriority::deleteByTrunk($trunk);
            if (isset($this->request['priorities'])) {
                $order = 1;
                foreach ($this->request['priorities'] as $priorityData) {
                    $priority = TrunkPriority::create($trunk, $priorityData);
                    $priority->order = $order;
                    if (!$priority->save()) {
                        throw new FormValidationException($priority);
                    }
                    $order++;
                }
            }

            TrunkTrunkRule::deleteByTrunk($trunk);
            if (isset($this->request['trunkRules'])) {
                $order = 1;
                foreach ($this->request['trunkRules'] as $ruleData) {
                    $rule = TrunkTrunkRule::create($trunk, $ruleData);
                    $rule->order = $order;
                    if (!$rule->save()) {
                        throw new FormValidationException($rule);
                    }
                    $order++;
                }
            }

            TrunkNumberPreprocessing::deleteByTrunk($trunk);
            if (isset($this->request['numberPreprocessing'])) {
                $order = 1;
                foreach ($this->request['numberPreprocessing'] as $ruleData) {
                    $rule = TrunkNumberPreprocessing::create($trunk, $ruleData);
                    $rule->order = $order;
                    if (!$rule->save()) {
                        throw new FormValidationException($rule);
                    }
                    $order++;
                }
            }

            TrunkABfiltersRule::deleteByTrunk($trunk);
            if (isset($this->request['numbersRules'])) {
                foreach ($this->request['numbersRules'] as $ruleKey => $data) {
                    $order = 1;
                    foreach ($data as $row) {
                        $rule = TrunkABfiltersRule::create($trunk, $row);
                        $rule->order = $order;
                        if (!$rule->save()) {
                            throw new FormValidationException($rule);
                        }
                        $order++;
                    }
                }
            }
    
            TrunkLoadLimit::deleteByTrunk($trunk);
            if (isset($this->request['loadLimit'])) {
                $order = 1;
                foreach ($this->request['loadLimit'] as $row) {
                    if (!isset($row['limit_mode'])) {
                        $row['limit_absolute'] = null;
                        $row['limit_relative'] = null;
                    } elseif ($row['limit_mode'] == TrunkLoadLimit::LOAD_LIMIT_TYPE_ABSOLUTE) {
                        $row['limit_absolute'] = $row['limit_value'];
                        $row['limit_relative'] = null;
                    } else {
                        $row['limit_absolute'] = null;
                        $row['limit_relative'] = $row['limit_value'];
                    }
                    
                    $limit = TrunkLoadLimit::create($trunk, $row);
                    $limit->order = $order;
                    if (!$limit->save()) {
                        throw new FormValidationException($limit);
                    }
                    $order++;
                }
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive()) {
                $transaction->rollBack();
            }
        }
    }
    
    /**
     * @return array
     * @throws HttpException
     */
    public function actionFindUsagesInTrunkGroups()
    {
        if (!\Yii::$app->user->can('trunk_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $number = $this->getTrunkOr404($this->request['id']);
        
        return $number->findUsagesInTrunkGroups();
    }

    /**
     * @throws StaleObjectException
     * @throws HttpException
     * @throws \Exception
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('trunk_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $trunk = $this->getTrunkOr404($this->request['id']);
        $trunk->delete();
    }
    
    /**
     * @return \app\models\Trunk[]
     * @throws HttpException
     */
    public function actionReadMarketplace()
    {
        if (!\Yii::$app->user->can('marketplace_list') && !\Yii::$app->user->can('marketplace_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $marketPlaceId = $this->request['market_place_id'];
        
        $items = Trunk::find()
            ->alias('t')
            ->select([
                't.id p_trunk_id',
                new Expression('concat(s.id, \': \', s.name) as server_name'),
                new Expression('case when h.id is not null then concat(h.id, \': \', h.name) else \'Без хаба\' end as hub_name'),
                new Expression('concat(t.id, \': \', t.trunk_name) as p_trunk_name'),
                'h.id as hub_id',
                's.id as server_id',
                'st.client_account_id',
                'st.id l_trunk_id',
                'st.id price_name_basic',
                's.name as server_name_basic',
                new Expression('case when h.id is not null then h.name else \'Без хаба\' end as hub_name_basic'),
                't.trunk_name as p_trunk_name_basic',
                'st.id as l_trunk_name_basic',
                'st.description as organization_name',
                'str.uplink_enabled',
                'str.trunk_groups',
                'tg.trunk_group_name'
            ])
            ->innerJoin('public.server s', 's.id = t.server_id')
            ->innerJoin('billing.service_trunk st', 't.id = st.trunk_id')
            ->innerJoin('billing.service_trunk_settings sts', 'sts.trunk_id = st.id')
            ->innerJoin('auth.hub h', 'h.id = s.hub_id')
            ->leftJoin('auth.service_trunk_routing str', 'str.id = st.id')
            ->leftJoin('billing.clients bc', 'bc.id = st.client_account_id')
            ->leftJoin('billing.organization bo', 'bo.id = bc.organization_id')
            ->leftJoin('(select str.id, string_agg(name, \', \') as trunk_group_name from auth.trunk_group tg join auth.service_trunk_routing str on tg.id = any(str.trunk_groups) group by str.id) as tg', 'tg.id = str.id')
            ->where('sts.type = ' . self::TYPE_TERMINATION)
            ->andWhere('h.market_place_id = :market_place_id')
            ->andWhere('t.uplink_trunk = true')
            ->andWhere('st.expire_dt > now()')
            ->orderBy('t.server_id, sts.id')
            ->indexBy('price_name_basic')
            ->addParams([':market_place_id' => $marketPlaceId])
            ->asArray()
            ->all();

        $result = [];
        
        foreach ($items as $item) {
            $result[$item['hub_name_basic']]['items'][$item['server_name_basic']]['items'][$item['p_trunk_name_basic']]['items'][] = $item;
            $result[$item['hub_name_basic']]['id'] = $item['hub_id'];
            $result[$item['hub_name_basic']]['items'][$item['server_name_basic']]['id'] = $item['server_id'];
            $result[$item['hub_name_basic']]['items'][$item['server_name_basic']]['items'][$item['p_trunk_name_basic']]['id'] = $item['p_trunk_id'];
        }
        
        return $result;
    }
}
