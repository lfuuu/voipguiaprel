<?php

namespace app\controllers\json;

use app\models\RouteTableRoute;
use Yii;
use app\models\RouteTable;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class RouteTableController extends JsonController
{
    public function actionList() {

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

    public function actionRead() {
        $server = $this->getServerOr404($this->request['server_id']);
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;

        return
            RouteTable::find()
                ->select(['id', 'name','sw_shared'])
                ->where("( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id)
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        $item = RouteTable::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'Таблица маршрутизации не найдена');
        }
        $data = $item->toArray();

        $routes =
            RouteTableRoute::find()
                ->where(['route_table_id' => $item->id])
                ->orderBy('order')
        ;

        $data['routes'] = $routes->asArray()->all();

        return $data;
    }

    public function actionSave()
    {
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            $routeTable = $this->getRouteTableOr404($this->request['id']);
        } else {
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

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }

    public function actionDelete()
    {
        $item = RouteTable::findOne($this->request['id']);
        $item->delete();
    }
}
