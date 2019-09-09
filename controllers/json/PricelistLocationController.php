<?php

namespace app\controllers\json;

use app\models\billing_uu\PricelistLocation;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class PricelistLocationController extends JsonController
{
    public function actionGet()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            PricelistLocation::find()
                ->where(['id' => $this->request['id']])
                ->asArray()
                ->one();
    }
    
    public function actionListByPricelist()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $pricelistId = $this->request['pricelist_id'];
        
        return
            PricelistLocation::find()
                ->select(['id' => 'id', 'name' => 'id'])
                ->where(['pricelist_id' => $pricelistId])
                ->asArray()
                ->all();
    }
    
    public function actionSave()
    {
        if (!\Yii::$app->user->can('pricelist_edit') && !\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $result = [];
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('pricelist_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getPricelistLocationOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('pricelist_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = PricelistLocation::create();
            $result['log'] = ['data_before' => []];
        }
        
        $item->load($this->request, '');
        
        $transaction = PricelistLocation::getDb()->beginTransaction();
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
    
    /**
     * @throws StaleObjectException
     * @throws HttpException
     * @throws \Exception
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('pricelist_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getPricelistLocationOr404($this->request['id']);
        $item->delete();
    }
}
