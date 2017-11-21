<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\billing\ServiceTrunk;
use app\models\Trunk;
use app\models\TrunkABfiltersRule;
use app\models\TrunkNumberPreprocessing;
use app\models\TrunkPriority;
use app\models\TrunkTrunkRule;
use Yii;
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
                    'road_to_regions', 'load_warning', 'orig_enabled', 'term_enabled', 'tech_trunk', 'pstn_trunk', 'mgmn_trunk', 'mgmn_orig_trunk', 'le8accept',
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
                ->where(['id' => $this->request['id']])
                ->asArray()
                ->one();
        if ($item === null) {
            throw new HttpException(404, 'Транк не найден');
        }

        return $item;
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
