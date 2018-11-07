<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\auth\Header;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class HeaderController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('header_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        return
            Header::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead()
    {
        if (!\Yii::$app->user->can('header_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            Header::find()
                ->select(['id', 'name', 'description', 'value'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('header_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = Header::findOne($this->request['id']);
        
        if ($item === null) {
            throw new HttpException(404, 'CPC не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('header_edit') && !\Yii::$app->user->can('header_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('header_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getHeaderOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('header_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = Header::create();
        }

        $item->load($this->request, '');

        $transaction = Header::getDb()->beginTransaction();
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

    public function actionDelete()
    {
        if (!\Yii::$app->user->can('header_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = Header::findOne($this->request['id']);
        $item->delete();
    }
}
