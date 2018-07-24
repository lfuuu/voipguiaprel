<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\sorm\Commutator;
use app\models\sorm\Operator;
use app\models\voip\Pricelist;
use app\models\billing\ServiceTrunk;
use app\models\Trunk;
use app\models\sorm\Trunk as TrunkSorm;
use app\models\TrunkABfiltersRule;
use app\models\TrunkNumberPreprocessing;
use app\models\TrunkPriority;
use app\models\TrunkTrunkRule;
use Yii;
use yii\db\Query;
use yii\db\StaleObjectException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\db\Expression;

class TrunkController extends JsonController
{

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
                    'mgmn_trunk', 'mgmn_orig_trunk', 'le8accept',
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
        
        $item =
            Trunk::find()
                ->with('priorities')
                ->with('trunkRules')
                ->with('numberPreprocessing')
                ->with('numbersRules')
                ->with('trunkSorm')
                ->where(['id' => $this->request['id']])
                ->asArray()
                ->one();
        if ($item === null) {
            throw new HttpException(404, 'Транк не найден');
        }
        
        $item['default_auto_routing'] = $item['auto_routing'];
        $item['do_sync'] = false;
        
        if ($item['auto_routing']) {
            $count = Pricelist::find()
                ->leftJoin('billing.service_trunk_settings sts', 'voip.pricelist.id = sts.pricelist_id')
                ->leftJoin('billing.service_trunk st', 'sts.trunk_id = st.id')
                ->leftJoin('auth.trunk t', 'st.trunk_id = t.id')
                ->where('st.term_enabled is true')
                ->andWhere('voip.pricelist.is_global is true')
                ->andWhere(['st.trunk_id' => $this->request['id']])
                ->count();
            
            if ($count > 0) {
                $item['do_sync'] = true;
            }
        } else {
            $count = Pricelist::find()
                ->leftJoin('billing.service_trunk_settings sts', 'voip.pricelist.id = sts.pricelist_id')
                ->leftJoin('billing.service_trunk st', 'sts.trunk_id = st.id')
                ->leftJoin('auth.trunk t', 'st.trunk_id = t.id')
                ->where('st.term_enabled is true')
                ->andWhere('voip.pricelist.backup_is_global is true and voip.pricelist.is_global is false')
                ->andWhere(['st.trunk_id' => $this->request['id']])
                ->count();
    
            if ($count > 0) {
                $item['do_sync'] = true;
            }
        }

        return $item;
    }
    
    /**
     * @return array
     * @throws HttpException
     */
    private function toggleAutorouting($on, $trunkId)
    {
        $doSync = false;
        
        if ($on) {
            $items = Pricelist::find()
                ->leftJoin('billing.service_trunk_settings sts', 'voip.pricelist.id = sts.pricelist_id')
                ->leftJoin('billing.service_trunk st', 'sts.trunk_id = st.id')
                ->leftJoin('auth.trunk t', 'st.trunk_id = t.id')
                ->where('st.term_enabled is true')
                ->andWhere('voip.pricelist.is_global is false')
                ->andWhere(['st.trunk_id' => $trunkId])
                ->all();
    
            foreach ($items as $item) {
                if ($item->backup_is_global && !$item->is_global) {
                    $doSync = true;
                }
                
                $item->is_global = is_null($item->backup_is_global) ? false : $item->backup_is_global;
                $item->save();
            }
        } else {
            $items = Pricelist::find()
                ->leftJoin('billing.service_trunk_settings sts', 'voip.pricelist.id = sts.pricelist_id')
                ->leftJoin('billing.service_trunk st', 'sts.trunk_id = st.id')
                ->leftJoin('auth.trunk t', 'st.trunk_id = t.id')
                ->where('st.term_enabled is true')
                ->andWhere('voip.pricelist.is_global is true')
                ->andWhere(['st.trunk_id' => $trunkId])
                ->all();
    
            foreach ($items as $item) {
                if ($item->is_global) {
                    $doSync = true;
                }
                
                $item->backup_is_global = $item->is_global;
                $item->is_global = false;
                $item->save();
            }
        }

        if ($doSync) {
            (new Query())->select(new Expression('event.notify(\'defs-manual\',0)'))->all();
            (new Query())->select(new Expression('event.notify(\'pricelist-manual\',0)'))->all();
        }
    }
    
    private function toggleSorm($trunk, $data)
    {
        $trunkSorm = TrunkSorm::find()
            ->where(['code_trunk' => $trunk->id])
            ->one();
        
        switch (true) {
            case ($trunkSorm && $data['enabled']):
                //edit
                $trunkSorm->name = $data['name'];
                $trunkSorm->is_show = isset($data['is_show']) ? $data['is_show'] : false;
                $trunkSorm->groups = $data['groups'] ? '{' . implode(',', $data['groups']) . '}' : '{}';
                
                $trunkSorm->save();
                
                break;
            case ($trunkSorm && !$data['enabled']):
                //delete
                $trunkSorm->delete();
                break;
            case (!$trunkSorm && $data['enabled']):
                //create
                $operator = Operator::find()
                    ->with('commutator')
                    ->where(['server_id' => $trunk->server_id])
                    ->one();
                
                $dataToCreate = [
                    'operator_id' => $operator->id,
                    'code_trunk' => $trunk->id,
                    'ats_mnemo_code' => $operator->commutator->comutator_str_id,
                    'type' => 2,
                    'start_date' => date('Y-m-d H:i:s'),
                    'name' => $data['name'],
                    'old_name' => $trunk->name,
                    'is_show' => isset($data['is_show']) ? $data['is_show'] : false,
                    'groups' => $data['groups'] ? '{' . implode(',', $data['groups']) . '}' : '{}',
                    'region_id' => $trunk->server_id
                ];
                
                $trunkSorm = TrunkSorm::create($dataToCreate);
                $trunkSorm->save();
                
                break;
            case (!$trunkSorm && !$data['enabled']):
                //do_nothing
                break;
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
    
            if ($trunk->isAttributeChanged('auto_routing')) {
                $this->toggleAutorouting($trunk->auto_routing, $trunk->id);
            }
    
            $this->toggleSorm($trunk, $this->request['sorm']);

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

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive()) {
                $transaction->rollBack();
            }
        }
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
}
