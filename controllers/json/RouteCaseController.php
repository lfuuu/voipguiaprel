<?php

namespace app\controllers\json;

use app\models\RouteCaseOperator;
use Yii;
use app\classes\JsonController;
use app\models\RouteCase;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class RouteCaseController extends JsonController
{
    public function actionList() {
        $version = $this->getVersionOr404($this->request['config_version_id']);

        return
            RouteCase::find()
                ->select(['id', 'name'])
                ->where(['config_version_id' => $version->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $version = $this->getVersionOr404($this->request['config_version_id']);

        return
            RouteCase::find()
                ->with('operators')
                ->with('operators.operator')
                ->select(['id', 'name'])
                ->where(['config_version_id' => $version->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        $item =
            RouteCase::find()
                ->with('operators')
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
        $version = $this->getVersionForUpdateOr404($this->request['config_version_id']);

        if (isset($this->request['id'])) {
            $routeCase = $this->getRouteCaseOr404($this->request['id']);
        } else {
            $routeCase = RouteCase::create($version);
        }

        $routeCase->load($this->request, '');

        $transaction = RouteCase::getDb()->beginTransaction();
        try {
            if (!$routeCase->save()) {
                throw new FormValidationException($routeCase);
            }

            RouteCaseOperator::deleteByRouteCase($routeCase);
            foreach ($this->request['operators'] as $operatorData) {
                $operator = RouteCaseOperator::create($routeCase, $operatorData);
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
        $this->getVersionForUpdateOr404($item->config_version_id);
        $item->delete();
    }
}
