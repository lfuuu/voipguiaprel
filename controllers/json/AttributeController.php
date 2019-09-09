<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\Attribute;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class AttributeController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('attribute_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            Attribute::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead()
    {
        if (!\Yii::$app->user->can('attribute_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        return
            Attribute::find()
                ->select(['id', 'name', 'note', 'object_comment'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('attribute_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = Attribute::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'Attribute не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('attribute_edit') && !\Yii::$app->user->can('attribute_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $result = [];
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('attribute_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getAttributeOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('attribute_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = Attribute::create();
            $result['log'] = ['data_before' => []];
        }

        $item->load($this->request, '');

        $transaction = Attribute::getDb()->beginTransaction();
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
        if (!\Yii::$app->user->can('attribute_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = Attribute::findOne($this->request['id']);
        $item->delete();
    }
}
