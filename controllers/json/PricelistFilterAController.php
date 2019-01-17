<?php

namespace app\controllers\json;

use app\models\billing_uu\Major;
use app\models\billing_uu\PricelistFilterA;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\db\Expression;
use yii\db\Query;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class PricelistFilterAController extends JsonController
{
    public function actionGet()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            PricelistFilterA::find()
                ->alias('a')
                ->select(
                    ['a.*', 'date_trunc(\'second\', time_start) as time_start',
                    'date_trunc(\'second\', time_end) as time_end',
                    'filter_country' => 'm.country_code']
                )
                ->leftJoin(['m' => Major::tableName()], 'm.id = a.nnp_filter')
                ->where(['a.id' => $this->request['id']])
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
            
            $item = $this->getPricelistFilterAOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('pricelist_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = PricelistFilterA::create();
        }
        
        $item->load($this->request, '');
        
        $transaction = PricelistFilterA::getDb()->beginTransaction();
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
    
        $item = $this->getPricelistFilterAOr404($this->request['id']);
        $item->load($this->request, '');
    
        $transaction = PricelistFilterA::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }
    
            (new Query())->select(new Expression('billing_uu.copy_a_nnp_filter(:filter_a_id)'))
                ->addParams([
                    ':filter_a_id' => $this->request['id']
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
        
        $item = $this->getPricelistFilterAOr404($this->request['id']);
        $item->delete();
    }
}
