<?php

namespace app\controllers\json;

use app\classes\ConfigExporter;
use app\models\RouteCaseTrunk;
use Yii;
use app\classes\JsonController;
use app\models\RouteCase;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class RouteCaseController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('route_case_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;

        return
            RouteCase::find()
                ->select(['id', 'name'])
                ->where("( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id)
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead()
    {
        if (!\Yii::$app->user->can('route_case_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;

        return
            RouteCase::find()
                ->with('trunks')
                ->with('trunks.trunk')
                ->select(['id', 'name','server_id','sw_shared'])
                ->where("( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id)
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('route_case_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item =
            RouteCase::find()
                ->with('trunks')
                ->where(['id' => $this->request['id']])
                ->asArray()
                ->one();
        if ($item === null) {
            throw new HttpException(404, 'AIRP не найден');
        }

        return $item;
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('route_case_edit') && !\Yii::$app->user->can('route_case_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('route_case_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $routeCase = $this->getRouteCaseOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('route_case_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $routeCase = RouteCase::create($server);
        }

        $routeCase->load($this->request, '');

        $transaction = RouteCase::getDb()->beginTransaction();
        try {
            if (!$routeCase->save()) {
                throw new FormValidationException($routeCase);
            }

            RouteCaseTrunk::deleteByRouteCase($routeCase);
            foreach ($this->request['trunks'] as $operatorData) {
                $operator = RouteCaseTrunk::create($routeCase, $operatorData);
                if (!$operator->save()) {
                    throw new FormValidationException($operator);
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
        if (!\Yii::$app->user->can('route_case_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = RouteCase::findOne($this->request['id']);
        $item->delete();
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionFindUsagesInOutcomes()
    {
        if (!\Yii::$app->user->can('route_case_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $group = $this->getRouteCaseOr404($this->request['id']);

        return $group->findUsagesInOutcomes();
    }
}
