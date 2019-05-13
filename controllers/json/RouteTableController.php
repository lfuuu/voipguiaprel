<?php

namespace app\controllers\json;

use app\models\RouteRouteRule;
use app\models\RouteTableRoute;
use Yii;
use app\models\RouteTable;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class RouteTableController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('route_table_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;

        return
            RouteTable::find()
                ->select(['id', 'name'])
                ->where("( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id)
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead()
    {
        if (!\Yii::$app->user->can('route_table_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;

        return
            RouteTable::find()
                ->select(['id', 'name', 'server_id', 'sw_shared', 'object_comment'])
                ->where("( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id)
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('route_table_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = RouteTable::find()
            ->with('routeRules')
            ->with('routes')
            ->where(['id' => $this->request['id']])
            ->asArray()
            ->one();
        
        if ($item === null) {
            throw new HttpException(404, 'Таблица маршрутизации не найдена');
        }

        return $item;
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('route_table_edit') && !\Yii::$app->user->can('route_table_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('route_table_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $routeTable = $this->getRouteTableOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('route_table_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $routeTable = RouteTable::create($server);
        }

        $routeTable->load($this->request, '');

        $transaction = RouteTable::getDb()->beginTransaction();
        try {
            if (!$routeTable->save()) {
                throw new FormValidationException($routeTable);
            }

            RouteTableRoute::deleteByRouteTable($routeTable);
            $order = 1;
            foreach ($this->request['routes'] as $routeData) {
                $route = RouteTableRoute::create($routeTable, $routeData);
                $route->order = $order;
                if (!$route->save()) {
                    throw new FormValidationException($route);
                }
                $order++;
            }
    
            RouteRouteRule::deleteByRouteTable($routeTable);
            $order = 1;
            foreach ($this->request['routeRules'] as $routeData) {
                $route = RouteRouteRule::create($routeTable, $routeData);
                $route->order = $order;
                if (!$route->save()) {
                    throw new FormValidationException($route);
                }
                $order++;
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }

    public function actionDelete()
    {
        if (!\Yii::$app->user->can('route_table_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = RouteTable::findOne($this->request['id']);
        $item->delete();
    }
}
