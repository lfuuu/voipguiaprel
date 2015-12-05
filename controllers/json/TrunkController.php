<?php

namespace app\controllers\json;

use app\models\TrunkNumberPreprocessing;
use app\models\TrunkTrunkRule;
use Yii;
use app\classes\JsonController;
use app\models\TrunkPriority;
use app\models\TrunkRule;
use app\models\Trunk;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class TrunkController extends JsonController
{
    public function actionList() {
        $server = $this->getServerOr404($this->request['server_id']);

        return
            Trunk::find()
                ->select(['id', 'name'])
                ->where(['server_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $server = $this->getServerOr404($this->request['server_id']);

        return
            Trunk::find()
                ->select([
                    'id', 'name', 'code', 'trunk_name', 'trunk_name_alias',
                    'default_priority', 'source_rule_default_allowed', 'destination_rule_default_allowed',
                    'auto_routing', 'our_trunk', 'auth_by_number', 'orig_redirect_number', 'term_redirect_number', 'show_in_stat', 'route_table_id'
                ])
                ->with('routeTable')
                ->where(['server_id' => $server->id])
                ->orderBy('trunk_name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        $item =
            Trunk::find()
                ->with('priorities')
                ->with('rules')
                ->with('trunkRules')
                ->with('numberPreprocessing')
                ->where(['id' => $this->request['id']])
                ->asArray()
                ->one();
        if ($item === null) {
            throw new HttpException(404, 'Транк не найден');
        }

        return $item;
    }

    public function actionSave()
    {
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            $trunk = $this->getTrunkOr404($this->request['id']);
        } else {
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

            TrunkRule::deleteByTrunk($trunk);
            if (isset($this->request['rules'])) {
                $order = 1;
                foreach ($this->request['rules'] as $ruleData) {
                    $rule = TrunkRule::create($trunk, $ruleData);
                    $rule->order = $order;
                    if (!$rule->save()) {
                        throw new FormValidationException($rule);
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

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }

    public function actionDelete()
    {
        $item = Trunk::findOne($this->request['id']);
        $item->delete();
    }
}
