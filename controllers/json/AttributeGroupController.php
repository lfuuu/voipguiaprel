<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\AttributeGroup;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class AttributeGroupController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('attribute_group_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            AttributeGroup::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead()
    {
        if (!\Yii::$app->user->can('attribute_group_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            AttributeGroup::find()
                ->select(['id', 'name', 'attributeslist_ids','note'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('attribute_group_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = AttributeGroup::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'AttributeGroup не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('attribute_group_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getAttributeGroupOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('attribute_group_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = AttributeGroup::create();
        }

        $item->load($this->request, '');

        $item->setAttributesLists($this->request['attributeslist_ids']);

        $transaction = AttributeGroup::getDb()->beginTransaction();
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
        if (!\Yii::$app->user->can('attribute_group_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = AttributeGroup::findOne($this->request['id']);
        $item->delete();
    }
}
