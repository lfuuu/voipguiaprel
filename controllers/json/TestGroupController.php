<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\models\TestGroup;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class TestGroupController extends JsonController
{
    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws HttpException
     */
    public function actionList()
    {
        if (!\Yii::$app->user->can('test_group_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            TestGroup::find()
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
        if (!\Yii::$app->user->can('test_group_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            TestGroup::find()
                ->select(['id', 'name', 'object_comment'])
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
        if (!\Yii::$app->user->can('test_group_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = TestGroup::findOne($this->request['id']);

        if ($item === null) {
            throw new HttpException(404, 'TestGroup не найден');
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
        if (!\Yii::$app->user->can('test_group_edit') && !\Yii::$app->user->can('test_group_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('test_group_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getTestGroupOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('test_group_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = TestGroup::create();
        }

        $item->load($this->request, '');

        $transaction = TestGroup::getDb()->beginTransaction();
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

    /**
     * @inheritdoc
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('test_group_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = TestGroup::findOne($this->request['id']);
        $item->delete();
    }
}
