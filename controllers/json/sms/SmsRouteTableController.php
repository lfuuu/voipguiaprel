<?php

namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\auth\SmsRouteTable;
use app\models\auth\SmsRouteTableRoute;
use yii\web\ForbiddenHttpException;
use yii\db\Transaction;

class SmsRouteTableController extends JsonController
{
    protected $modelName        = 'app\models\auth\SmsRouteTable';
    protected $idParamName      = 'id';
    protected $nameParamName    = 'name';
    protected $withDependencies = ['routes'];
    protected $readWhere        = ['server_id'];
    protected $createPermission = 'sms_route_table_create';
    protected $listPermission   = 'sms_route_table_list';
    protected $editPermission   = 'sms_route_table_edit';
    protected $deletePermission = 'sms_route_table_delete';

    protected function getDataForLog($item)
    {
        $data = $item->toArray();
        $data['routes'] = [];
        foreach ($item->routes as $route) {
            $data['routes'][] = $route->toArray();
        }
        return $data;
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can(
             isset($this->request['id'])
               ? $this->editPermission
               : $this->createPermission
           )) {
            throw new ForbiddenHttpException('Access denied');
        }

        $isNew = empty($this->request['id']);
        if ($isNew) {
            $item = SmsRouteTable::create();
        } else {
            $item = SmsRouteTable::findOne($this->request['id']);
            if (!$item) {
                throw new \yii\web\HttpException(404, 'SmsRouteTable not found');
            }
        }

        $item->load($this->request, '');

        $transaction = SmsRouteTable::getDb()->beginTransaction(Transaction::SERIALIZABLE);
        try {
            $dataBefore = $this->getDataForLog($item);

            if (!$item->save()) {
                throw new FormValidationException($item);
            }

            SmsRouteTableRoute::deleteByRouteTable($item);
            $order = 1;
            foreach ($this->request['routes'] as $routeData) {
                $route = SmsRouteTableRoute::create($item, $routeData);
                $route->order = $order++;
                if (!$route->save()) {
                    throw new FormValidationException($route);
                }
            }

            $transaction->commit();
        } catch (\Exception $e) {
            if ($transaction->isActive) {
                $transaction->rollBack();
            }
            throw $e;
        }

        $item->refresh();              
        $item = SmsRouteTable::find()
            ->with('routes')
            ->where(['id' => $item->id])
            ->one();

        $dataAfter = $this->getDataForLog($item);

        return [
            'log' => [
                'data_before' => $dataBefore,
                'data_after'  => $dataAfter,
            ]
        ];
    }
}
