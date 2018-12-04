<?php

namespace app\controllers\json;

use app\models\billing_uu\Major;
use app\models\billing_uu\PricelistFilterB;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\db\Expression;
use yii\db\Query;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class PricelistFilterBController extends JsonController
{
    public function actionGet()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            PricelistFilterB::find()
                ->alias('b')
                ->select(
                    ['b.*', 'date_trunc(\'second\', time_start) as time_start',
                    'date_trunc(\'second\', time_end) as time_end',
                    'filter_country' => 'm.country_code']
                )
                ->leftJoin(['m' => Major::tableName()], 'm.id = b.nnp_filter')
                ->where(['b.id' => $this->request['id']])
                ->asArray()
                ->one();
    }
    
    public function actionSave()
    {
        if (!\Yii::$app->user->can('pricelist_edit') && !\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('pricelist_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getPricelistFilterBOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('pricelist_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = PricelistFilterB::create();
        }
        
        $item->load($this->request, '');
        
        $transaction = PricelistFilterB::getDb()->beginTransaction();
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
    
    public function actionSaveAndUpdate()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getPricelistFilterBOr404($this->request['id']);
        $item->load($this->request, '');
        
        $transaction = PricelistFilterB::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }
            
            (new Query())->select(new Expression('billing_uu.copy_b_nnp_filter(:filter_b_id)'))
                ->addParams([
                    ':filter_b_id' => $this->request['id']
                ])->one();
            
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
        if (!\Yii::$app->user->can('pricelist_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getPricelistFilterBOr404($this->request['id']);
        $item->delete();
    }
}
