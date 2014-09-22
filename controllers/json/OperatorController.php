<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\OperatorPriority;
use app\models\OperatorRule;
use app\models\Operator;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class OperatorController extends JsonController
{
    public function actionList() {
        $version = $this->getVersionOr404($this->request['config_version_id']);

        return
            Operator::find()
                ->select(['id', 'name'])
                ->where(['config_version_id' => $version->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $version = $this->getVersionOr404($this->request['config_version_id']);

        return
            Operator::find()
                ->select(['id', 'name', 'code', 'openca', 'default_priority', 'source_rule_default_allowed', 'destination_rule_default_allowed', 'auto_routing'])
                ->where(['config_version_id' => $version->id])
                ->orderBy('code')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        $item =
            Operator::find()
                ->with('priorities')
                ->with('rules')
                ->where(['id' => $this->request['id']])
                ->asArray()
                ->one();
        if ($item === null) {
            throw new HttpException(404, 'Оператор не найден');
        }

        return $item;
    }

    public function actionSave()
    {
        $version = $this->getVersionForUpdateOr404($this->request['config_version_id']);

        if (isset($this->request['id'])) {
            $operator = $this->getOperatorOr404($this->request['id']);
        } else {
            $operator = Operator::create($version);
        }

        $operator->load($this->request, '');

        $transaction = Operator::getDb()->beginTransaction();
        try {
            if ($operator->isAttributeChanged('need_recalc_routing_report')) {
                $version->need_recalc_routing_report = true;
                if (!$version->save()) {
                    throw new FormValidationException($version);
                }
            }

            if (!$operator->save()) {
                throw new FormValidationException($operator);
            }

            OperatorPriority::deleteByOperator($operator);
            if (isset($this->request['priorities'])) {
                $order = 1;
                foreach ($this->request['priorities'] as $priorityData) {
                    $priority = OperatorPriority::create($operator, $priorityData);
                    $priority->order = $order;
                    if (!$priority->save()) {
                        throw new FormValidationException($priority);
                    }
                    $order++;
                }
            }


            OperatorRule::deleteByOperator($operator);
            if (isset($this->request['rules'])) {
                $order = 1;
                foreach ($this->request['rules'] as $ruleData) {
                    $rule = OperatorRule::create($operator, $ruleData);
                    $rule->order = $order;
                    if (!$rule->save()) {
                        return $rule->errors;
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
        $item = Operator::findOne($this->request['id']);
        $this->getVersionForUpdateOr404($item->config_version_id);
        $item->delete();
    }
}
