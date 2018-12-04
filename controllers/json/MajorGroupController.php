<?php

namespace app\controllers\json;

use app\models\billing_uu\MajorGroup;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class MajorGroupController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('major_group_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        return
            MajorGroup::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }
    
    public function actionRead()
    {
        if (!\Yii::$app->user->can('major_group_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            MajorGroup::find()
                ->orderBy('name')
                ->asArray()
                ->all();
    }
    
    public function actionGet()
    {
        if (!\Yii::$app->user->can('major_group_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            MajorGroup::find()
                ->where(['id' => $this->request['id']])
                ->asArray()
                ->one();
    }
    
    public function actionSave()
    {
        if (!\Yii::$app->user->can('major_group_edit') && !\Yii::$app->user->can('major_group_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('major_group_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getMajorGroupOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('major_group_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = MajorGroup::create();
        }
        
        $item->load($this->request, '');
        
        $transaction = MajorGroup::getDb()->beginTransaction();
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
        if (!\Yii::$app->user->can('major_group_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getMajorGroupOr404($this->request['id']);
        
        $item->delete();
    }
}
