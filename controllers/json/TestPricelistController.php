<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\auth\TestPricelist;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use Yii;

class TestPricelistController extends JsonController
{
    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws HttpException
     */
    public function actionList()
    {
        if (!\Yii::$app->user->can('test_pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
                
        return
            TestPricelist::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws HttpException
     */
    public function actionRead()
    {
        if (!\Yii::$app->user->can('test_pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return TestPricelist::find()
                ->select(['auth.test_pricelist.*', 'mcc.country as mcc_name', 'mnc.network as mnc_name', 'p.name as pricelist_name',
                    new Expression('case 
                        when auth.test_pricelist.location_id = 1 then \'Домашний регион\' 
                        when auth.test_pricelist.location_id = 2 then \'Гостевой регион\' 
                        when auth.test_pricelist.location_id = 3 then \'Международный регион\' 
                        end as location_name')])
                ->leftJoin('nnp.mcc as mcc', 'mcc.mcc = auth.test_pricelist.mcc')
                ->leftJoin('nnp.mnc as mnc', 'mnc.mnc = auth.test_pricelist.mnc')
                ->leftJoin('billing_uu.pricelist as p', 'p.id = auth.test_pricelist.pricelist_id')
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionGet()
    {
        if (!\Yii::$app->user->can('test_pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = TestPricelist::find()
            ->where(['id' => $this->request['id']])
            ->asArray()
            ->one();

        if ($item === null) {
            throw new HttpException(404, 'TestPricelist не найден');
        }

        return $item;
    }

    /**
     * @throws FormValidationException
     * @throws HttpException
     * @throws \yii\db\Exception
     */
    public function actionSave()
    {
        if (!\Yii::$app->user->can('test_pricelist_edit') && !\Yii::$app->user->can('test_pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('test_pricelist_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getTestPricelistOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('test_pricelist_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = TestPricelist::create();
        }

        $item->load($this->request, '');

        $transaction = TestPricelist::getDb()->beginTransaction();
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
     * @inheritdoc
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('test_pricelist_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = TestPricelist::findOne($this->request['id']);
        $item->delete();
    }
}
