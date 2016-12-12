<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\Attribute;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class AttributeController extends JsonController
{
    public function actionList()
    {

        return
            Attribute::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead()
    {

        return
            Attribute::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        $item = Attribute::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'Attribute не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {

        if (isset($this->request['id'])) {
            $item = $this->getAttributeOr404($this->request['id']);
        } else {
            $item = Attribute::create();
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
    }

    public function actionDelete()
    {
        $item = Attribute::findOne($this->request['id']);
        $item->delete();
    }
}
