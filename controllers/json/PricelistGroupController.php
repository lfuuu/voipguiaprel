<?php

namespace app\controllers\json;

use app\models\billing_uu\PricelistGroup;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class PricelistGroupController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('pricelist_group_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        return
            PricelistGroup::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }
    
    public function actionRead()
    {
        if (!\Yii::$app->user->can('pricelist_group_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            PricelistGroup::find()
                ->orderBy('name')
                ->asArray()
                ->all();
    }
    
    public function actionGet()
    {
        if (!\Yii::$app->user->can('pricelist_group_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            PricelistGroup::find()
                ->where(['id' => $this->request['id']])
                ->asArray()
                ->one();
    }
    
    public function actionSave()
    {
        if (!\Yii::$app->user->can('pricelist_group_edit') && !\Yii::$app->user->can('pricelist_group_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('pricelist_group_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getPricelistGroupOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('pricelist_group_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = PricelistGroup::create();
        }
        
        $item->load($this->request, '');
        
        $transaction = PricelistGroup::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }
            
            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }
    
    /**
     * @throws StaleObjectException
     * @throws HttpException
     * @throws \Exception
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('pricelist_group_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getPricelistGroupOr404($this->request['id']);
        
        $item->delete();
    }
}
