<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\auth\ServiceTrunkRouting;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class ServiceTrunkRoutingController extends JsonController
{
    /**
     * @throws FormValidationException
     * @throws HttpException
     * @throws \yii\db\Exception
     */
    public function actionSave()
    {
        if (!\Yii::$app->user->can('marketplace_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        if (isset($this->request['id'])) {
            $item = ServiceTrunkRouting::findOne($this->request['id']);
            
            if (empty($item)) {
                $item = ServiceTrunkRouting::create();
            }
        } else {
            $item = ServiceTrunkRouting::create();
        }
    
        $item->load($this->request, '');
        
        $item->id = $this->request['id'];
        
        if (isset($this->request['uplink_enabled'])) {
            $item->uplink_enabled = $this->request['uplink_enabled'];
        }
    
        if (isset($this->request['trunk_groups'])) {
            $item->trunk_groups = '{' . implode(',', $this->request['trunk_groups']) . '}';
        }

        $transaction = ServiceTrunkRouting::getDb()->beginTransaction();
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
    }
}
