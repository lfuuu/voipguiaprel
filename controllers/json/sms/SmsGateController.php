<?php

namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\auth\SmsGate;
use yii\web\ForbiddenHttpException;

class SmsGateController extends JsonController
{
    /**
     * @inheritdoc
     */
    protected $modelName       = 'app\models\auth\SmsGate';
    protected $idParamName     = 'id';
    protected $nameParamName   = 'name';
    protected $readWhere       = [];

    protected $createPermission = 'sms_trunk_create';
    protected $listPermission   = 'sms_trunk_list';
    protected $editPermission   = 'sms_trunk_edit';
    protected $deletePermission = 'sms_trunk_delete';

    /**
     * Список всех узлов передачи данных
     */
    public function actionRead()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;
        $items = $modelName::find()->all();

        return $items;
    }

    /**
     * Создание или редактирование узла передачи данных
     */
    public function actionSave()
    {
        $req = $this->request;
        $id  = isset($req['id']) ? (int)$req['id'] : null;
        $result = [];

        if ($id !== null) {
            $item = SmsGate::findOne($id);
            if ($item) {
                if (!\Yii::$app->user->can($this->editPermission)) {
                    throw new ForbiddenHttpException('Access denied');
                }
                $result['log']['data_before'] = $this->getDataForLog($item);
            } else {
                if (!\Yii::$app->user->can($this->createPermission)) {
                    throw new ForbiddenHttpException('Access denied');
                }
                $item = new SmsGate();
                $result['log']['data_before'] = [];
            }
        } else {
            if (!\Yii::$app->user->can($this->createPermission)) {
                throw new ForbiddenHttpException('Access denied');
            }
            $item = new SmsGate();
            $result['log']['data_before'] = [];
        }

        $item->load($req, '');

        $transaction = SmsGate::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }
            $transaction->commit();
        } finally {
            if ($transaction->getIsActive()) {
                $transaction->rollBack();
            }
        }

        $result['log']['data_after'] = $this->getDataForLog($item);
        return $result;
    }

}
