<?php

namespace app\controllers\json;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\sorm\Operator;
use app\models\TrunkLoadLimit;
use app\models\billing\ServiceTrunk;
use app\models\Trunk;
use app\models\sorm\Trunk as TrunkSorm;
use app\models\TrunkABfiltersRule;
use app\models\TrunkNumberPreprocessing;
use app\models\TrunkPriority;
use app\models\TrunkTrunkRule;
use app\models\TrunkTrunkRuleAntifraud;
use app\models\TrunkTrunkRuleRoutingNum;
use yii\base\ErrorException;
use yii\db\StaleObjectException;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\db\Expression;
use yii\web\Response;


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
                ->select(['id', 'name', 'trunk_name', 'sorm_p268_us_type'])
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
                    'mgmn_trunk', 'mgmn_orig_trunk', 'le8accept', 'mgmn2_orig', 'mgmn2_term', 'object_comment', 'rounding_type',
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
        if (!\Yii::$app->user->can('trunk_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $regionId = $this->request['region_id'];
        
        $item =
            Trunk::find()
                ->with(['priorities', 'trunkRules', 'numberPreprocessing', 'numbersRules', 'usagesInMarketplace', 'trunkRulesRn', 'trunkRulesAntifraud'])
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
            if (empty($data['sorm_operator_id'])) {
                throw new ErrorException('Sorm operator id must not be empty');
            }

            if (!isset($data['groups']) || empty($data['groups']) || (count($data['groups']) == 1 && $data['groups'][0] == '')) {
                $data['groups'] = [];
            }

            if ($data['ip_addr'] && !filter_var($data['ip_addr'], FILTER_VALIDATE_IP)) {
                throw new ErrorException('IP address is incorrect');
            }
            
            if (isset($data['access_trunk_ip']) && $data['access_trunk_ip'] && !filter_var($data['access_trunk_ip'], FILTER_VALIDATE_IP)) {
                throw new ErrorException('Access trunk IP is incorrect');
            }
            
            if (isset($data['core_trunk_ip']) && $data['core_trunk_ip'] && !filter_var($data['core_trunk_ip'], FILTER_VALIDATE_IP)) {
                throw new ErrorException('Core trunk IP is incorrect');
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
                    $data['sorm_operator_id'], $data['source_type_id'], $data['spc'],
                    (isset($data['access_trunk']) ? $data['access_trunk'] : ''),
                    (isset($data['access_trunk_ip']) ? $data['access_trunk_ip'] : ''),
                    (isset($data['access_trunk_name']) ? $data['access_trunk_name'] : ''),
                    (isset($data['core_trunk']) ? $data['core_trunk'] : ''),
                    (isset($data['core_trunk_ip']) ? $data['core_trunk_ip'] : ''),
                    (isset($data['core_trunk_name']) ? $data['core_trunk_name'] : ''),
                    $regionId, isset($item['object_comment']) ? $item['object_comment'] : null, isset($item['id']) ? $item['id'] : null);
            }
        }
    }
    
    private function processSormData (
        $trunk, $oldName, $name, $ipAddr, $isShow,
        $groups, $sormOperatorId, $sourceTypeId,
        $spc, $accessTrunk, $accessTrunkIp, $accessTrunkName,
        $coreTrunk, $coreTrunkIp, $coreTrunkName,
        $regionId, $objectComment = null,
        $id = null
    )
    {
        if (!is_null($id)) {
            $trunkSorm = TrunkSorm::find()
                ->where(['code_trunk' => $trunk->id, 'id' => $id])
                ->one();
        } else {
            $trunkSorm = false;
        }

        if (in_array("1", $sormOperatorId)) {
            $operator = Operator::find()
                ->where(['server_id' => $trunk->server_id])
                ->one();
        } else {
            $operator = null;
        }
    
        if ($trunkSorm) {
            //edit
            $trunkSorm->operator_id = $operator ? ($operator->id != 35 ? $operator->id : 3) : '';
            $trunkSorm->ats_mnemo_code = 'reg' . $regionId;
            $trunkSorm->name = $name;
            $trunkSorm->ip_addr = $ipAddr ? $ipAddr : null;
            $trunkSorm->is_show = isset($isShow) ? $isShow : false;
            $trunkSorm->groups = $groups ? '{' . implode(',', $groups) . '}' : '{}';
            
            if (in_array(1, $groups)) {
                $trunkSorm->access_trunk = $accessTrunk;
                $trunkSorm->access_trunk_ip = $accessTrunkIp;
                $trunkSorm->access_trunk_name = $accessTrunkName;
                $trunkSorm->core_trunk = $coreTrunk;
                $trunkSorm->core_trunk_ip = $coreTrunkIp;
                $trunkSorm->core_trunk_name = $coreTrunkName;
            } else {
                $trunkSorm->access_trunk = '';
                $trunkSorm->access_trunk_ip = '';
                $trunkSorm->access_trunk_name = '';
                $trunkSorm->core_trunk = '';
                $trunkSorm->core_trunk_ip = '';
                $trunkSorm->core_trunk_name = '';
            }
            
            $trunkSorm->old_name = $oldName;
            $trunkSorm->sorm_operator_id = $sormOperatorId ? '{' . implode(',', $sormOperatorId) . '}' : '{}';
            $trunkSorm->source_type_id = $sourceTypeId;
            $trunkSorm->spc = ($sourceTypeId == 3 ? $spc : '');
            $trunkSorm->object_comment = $objectComment;

            $trunkSorm->save();
        } else {
            //create
            $dataToCreate = [
                'operator_id' => $operator ? $operator->id : '',
                'code_trunk' => $trunk->id,
                'ats_mnemo_code' => 'reg' . $regionId,
                'type' => 2,
                'start_date' => date('Y-m-d H:i:s'),
                'name' => $name,
                'ip_addr' => $ipAddr ? $ipAddr : null,
                'old_name' => $oldName,
                'is_show' => isset($isShow) ? $isShow : false,
                'groups' => $groups ? '{' . implode(',', $groups) . '}' : '{}',
                'region_id' => $regionId,
                'sorm_operator_id' => $sormOperatorId ? '{' . implode(',', $sormOperatorId) . '}' : '{}',
                'source_type_id' => $sourceTypeId,
                'spc' => ($sourceTypeId == 3 ? $spc : ''),
                'object_comment' => $objectComment
            ];
            
            if (in_array(1, $groups)) {
                $dataToCreate['access_trunk'] = $accessTrunk;
                $dataToCreate['access_trunk_ip'] = $accessTrunkIp;
                $dataToCreate['access_trunk_name'] = $accessTrunkName;
                $dataToCreate['core_trunk'] = $coreTrunk;
                $dataToCreate['core_trunk_ip'] = $coreTrunkIp;
                $dataToCreate['core_trunk_name'] = $coreTrunkName;
            } else {
                $dataToCreate['access_trunk'] = '';
                $dataToCreate['access_trunk_ip'] = '';
                $dataToCreate['access_trunk_name'] = '';
                $dataToCreate['core_trunk'] = '';
                $dataToCreate['core_trunk_ip'] = '';
                $dataToCreate['core_trunk_name'] = '';
            }
    
            $trunkSorm = TrunkSorm::create($dataToCreate);
            $trunkSorm->save();
        }
    }

    /**
     * @return \app\models\billing\ServiceTrunk[]
     */
    public function actionGetServiceTrunks()
    {
        if (!\Yii::$app->user->can('trunk_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        if (isset($this->request['trunk_id']) && (int)$this->request['trunk_id']) {
            $result = ServiceTrunk::findActualByTrunkId($this->request['trunk_id']);
        }else {
            $result = [];
        }

        return $result;
   
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
    
        $result = [];
        
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('trunk_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $trunk = $this->getTrunkOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($trunk)];
        } else {
            if (!\Yii::$app->user->can('trunk_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $trunk = Trunk::create($server);
            $result['log'] = ['data_before' => []];
        }

        $trunk->load($this->request, '');

        if (isset($this->request['sorm_p268'])) {
            $sormP268 = $this->request['sorm_p268'];
            $trunk->sorm_p268_enabled = isset($sormP268['enabled']) ? $sormP268['enabled'] : $trunk->sorm_p268_enabled;
            $trunk->sorm_p268_us_type = isset($sormP268['us_type']) ? $sormP268['us_type'] : $trunk->sorm_p268_us_type;
            $trunk->sorm_p268_orm_id = isset($sormP268['orm_id']) ? $sormP268['orm_id'] : $trunk->sorm_p268_orm_id;
        }

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
                    if ($ruleData['ac_mode'] == true) {
                        $ruleData['ac_mode'] = 1;
                    } else {
                        $ruleData['ac_mode'] = 0;
                    }
                    $rule = TrunkTrunkRule::create($trunk, $ruleData);
                    $rule->order = $order;
                    if (!$rule->save()) {
                        throw new FormValidationException($rule);
                    }
                    $order++;
                }
            }

            TrunkTrunkRuleRoutingNum::deleteByTrunk($trunk);
            if (isset($this->request['trunkRulesRn'])) {
                $order = 1;
                foreach ($this->request['trunkRulesRn'] as $ruleData) {
                    if ($ruleData['ac_mode'] == true) {
                        $ruleData['ac_mode'] = 1;
                    } else {
                        $ruleData['ac_mode'] = 0;
                    }
                    $rule = TrunkTrunkRuleRoutingNum::create($trunk, $ruleData);
                    $rule->order = $order;
                    if (!$rule->save()) {
                        throw new FormValidationException($rule);
                    }
                    $order++;
                }
            }

            TrunkTrunkRuleAntifraud::deleteByTrunk($trunk);
            if (isset($this->request['trunkRulesAntifraud'])) {
                $order = 1;
                foreach ($this->request['trunkRulesAntifraud'] as $ruleData) {
                    if ($ruleData['ac_mode'] == true) {
                        $ruleData['ac_mode'] = 1;
                    } else {
                        $ruleData['ac_mode'] = 0;
                    }
                    $rule = TrunkTrunkRuleAntifraud::create($trunk, $ruleData);
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
    
        $result['log']['data_after'] = $this->getDataForLog($trunk);
    
        return $result;
    }
    
    /**
     * @return array
     * @throws HttpException
     */
    public function actionFindUsagesInTrunkGroups()
    {
        if (!\Yii::$app->user->can('trunk_list')) {
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
//            ->indexBy('price_name_basic')
            ->addParams([':market_place_id' => $marketPlaceId])
            ->asArray()
            ->all();

        $items = ArrayHelper::index($items, 'price_name_basic');

        $result = [];
        
        foreach ($items as $item) {
            $result[$item['hub_name_basic']]['items'][$item['server_name_basic']]['items'][$item['p_trunk_name_basic']]['items'][] = $item;
            $result[$item['hub_name_basic']]['id'] = $item['hub_id'];
            $result[$item['hub_name_basic']]['items'][$item['server_name_basic']]['id'] = $item['server_id'];
            $result[$item['hub_name_basic']]['items'][$item['server_name_basic']]['items'][$item['p_trunk_name_basic']]['id'] = $item['p_trunk_id'];
        }
        
        return $result;
    }
    public function actionCheckOrmId()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $orm_id = Yii::$app->request->post('orm_id');
        $region_id = Yii::$app->request->post('region_id');
        $trunk_id = Yii::$app->request->post('trunk_id');

        if (!$orm_id || !$region_id) {
            return ['error' => 'Недостаточно параметров'];
        }

        $query = Trunk::find()
            ->where([
                'sorm_p268_enabled' => true,
                'sorm_p268_orm_id' => $orm_id,
                'server_id' => $region_id,
            ]);

        if ($trunk_id) {
            $query->andWhere(['<>', 'id', $trunk_id]);
        }

        $existingTrunk = $query->one();

        if ($existingTrunk) {
            return [
                'exists' => true,
                'trunk_name' => $existingTrunk->name,
            ];
        } else {
            return [
                'exists' => false,
            ];
        }
    }
}
