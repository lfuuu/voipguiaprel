<?php

namespace app\controllers\json;

use app\models\RouteCaseTrunk;
use Yii;
use app\classes\JsonController;
use app\models\RouteCase;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class RouteCaseController extends JsonController
{
    public function actionList() {
        $server = $this->getServerOr404($this->request['server_id']);

        return
            RouteCase::find()
                ->select(['id', 'name'])
                ->where(['server_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $server = $this->getServerOr404($this->request['server_id']);

        return
            RouteCase::find()
                ->with('trunks')
                ->with('trunks.trunk')
                ->select(['id', 'name'])
                ->where(['server_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
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
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            $routeCase = $this->getRouteCaseOr404($this->request['id']);
        } else {
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
        $item = RouteCase::findOne($this->request['id']);
        $item->delete();
    }
}
