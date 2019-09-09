<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\models\auth\TestPricelistGroup;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class TestPricelistGroupController extends JsonController
{
    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws HttpException
     */
    public function actionList()
    {
        if (!\Yii::$app->user->can('test_pricelist_group_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            TestPricelistGroup::find()
                ->select(['id', 'name'])
                ->orderBy('id')
                ->asArray()
                ->all();
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws HttpException
     */
    public function actionRead()
    {
        if (!\Yii::$app->user->can('test_pricelist_group_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            TestPricelistGroup::find()
                ->select(['id', 'name'])
                ->orderBy('id')
                ->asArray()
                ->all();
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionGet()
    {
        if (!\Yii::$app->user->can('test_pricelist_group_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = TestPricelistGroup::findOne($this->request['id']);

        if ($item === null) {
            throw new HttpException(404, 'TestPricelistGroup не найден');
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
        if (!\Yii::$app->user->can('test_pricelist_group_edit') && !\Yii::$app->user->can('test_pricelist_group_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $result = [];
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('test_pricelist_group_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getTestPricelistGroupOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('test_pricelist_group_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = TestPricelistGroup::create();
            $result['log'] = ['data_before' => []];
        }

        $item->load($this->request, '');

        $transaction = TestPricelistGroup::getDb()->beginTransaction();
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

    /**
     * @inheritdoc
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('test_pricelist_group_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = TestPricelistGroup::findOne($this->request['id']);
        $item->delete();
    }
}
