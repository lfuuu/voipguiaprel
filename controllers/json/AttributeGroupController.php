<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\AttributeGroup;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class AttributeGroupController extends JsonController
{
    public function actionList()
    {

        return
            AttributeGroup::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead()
    {

        return
            AttributeGroup::find()
                ->select(['id', 'name', 'attributeslist_ids'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        $item = AttributeGroup::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'AttributeGroup не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {

        if (isset($this->request['id'])) {
            $item = $this->getAttributeGroupOr404($this->request['id']);
        } else {
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
        $item = AttributeGroup::findOne($this->request['id']);
        $item->delete();
    }
}
