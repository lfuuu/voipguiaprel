<?php

namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\auth\SmsRouteTable;
use app\models\auth\SmsTrunk;
use yii\web\ForbiddenHttpException;

class SmsController extends JsonController
{
    protected $modelName = 'app\models\auth\SmsTrunk';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $readWhere = ['server_id'];
    protected $createPermission = 'sms_trunk_create';
    protected $listPermission = 'sms_trunk_list';
    protected $editPermission = 'sms_trunk_edit';
    protected $deletePermission = 'sms_trunk_delete';

    public function actionRead()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $items =
            $modelName::find()
                ->all();

        return $items;
    }
    
    public function actionSave()
    {
        if (!\Yii::$app->user->can('sms_trunk_edit') && !\Yii::$app->user->can('sms_trunk_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $result = [];
        
        if (isset($this->request['id'])) {

            if (!\Yii::$app->user->can('sms_trunk_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getSmsOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('sms_trunk_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = SmsTrunk::create();
            $result['log'] = ['data_before' => []];
        }

        $item->load($this->request, '');
        $transaction = SmsTrunk::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    
        $result['log']['data_after'] = $this->getDataForLog($item);
    
        return $result;
    }
}