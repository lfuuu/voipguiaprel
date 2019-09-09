<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\Cpc;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class CpcController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('cpc_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        return
            Cpc::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead()
    {
        if (!\Yii::$app->user->can('cpc_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            Cpc::find()
                ->select(['id', 'name', 'description', 'object_comment'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('cpc_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = Cpc::findOne($this->request['id']);
        
        if ($item === null) {
            throw new HttpException(404, 'CPC не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('cpc_edit') && !\Yii::$app->user->can('cpc_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $result = [];
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('cpc_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getCpcOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('cpc_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = Cpc::create();
            $result['log'] = ['data_before' => []];
        }

        $item->load($this->request, '');

        $transaction = Cpc::getDb()->beginTransaction();
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

    public function actionDelete()
    {
        if (!\Yii::$app->user->can('cpc_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = Cpc::findOne($this->request['id']);
        $item->delete();
    }
}
