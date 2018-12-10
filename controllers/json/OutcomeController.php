<?php

namespace app\controllers\json;

use app\models\auth\OutcomeRule;
use Yii;
use app\classes\JsonController;
use app\models\Outcome;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class OutcomeController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('outcome_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;

        return
            Outcome::find()
                ->select(['id', 'name'])
                ->where("( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id)
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead()
    {
        if (!\Yii::$app->user->can('outcome_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;

        return
            Outcome::find()
                ->with('routeCase')
                ->with('releaseReason')
                ->with('airp')
                ->select(['id', 'name', 'type_id', 'route_case_id', 'release_reason_id', 'airp_id', 'calling_station_id', 'called_station_id','server_id','sw_shared','ocpn','rn'])
                ->where("( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id)
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('outcome_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = Outcome::find()
            ->with('outcomeRules')
            ->where(['id' => $this->request['id']])
            ->asArray()
            ->one();
        
        if ($item === null) {
            throw new HttpException(404, 'Outcome не найден');
        }

        return $item;
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('outcome_edit') && !\Yii::$app->user->can('outcome_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('outcome_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $outcome = $this->getOutcomeOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('outcome_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $outcome = Outcome::create($server);
        }

        $outcome->load($this->request, '');
        
        $transaction = Outcome::getDb()->beginTransaction();
        try {
            if (!$outcome->save()) {
                throw new FormValidationException($outcome);
            }
    
            OutcomeRule::deleteByOutcome($outcome);
            if (isset($this->request['outcomeRules'])) {
                $order = 1;
                foreach ($this->request['outcomeRules'] as $ruleData) {
                    $rule = OutcomeRule::create($outcome, $ruleData);
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
        if (!\Yii::$app->user->can('outcome_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = Outcome::findOne($this->request['id']);
        $item->delete();
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionFindUsagesInRouteTables()
    {
        if (!\Yii::$app->user->can('outcome_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $group = $this->getOutcomeOr404($this->request['id']);

        return $group->findUsagesInRouteTables();
    }
}
