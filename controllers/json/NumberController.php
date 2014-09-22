<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\Number;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class NumberController extends JsonController
{
    public function actionList() {
        $version = $this->getVersionOr404($this->request['config_version_id']);

        return
            Number::find()
                ->select(['id', 'name', 'type_id'])
                ->where(['config_version_id' => $version->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $version = $this->getVersionOr404($this->request['config_version_id']);

        return
            Number::find()
                ->select(['id', 'name', 'type_id', 'prefixlist_ids'])
                ->where(['config_version_id' => $version->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        $item = Number::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'Номер не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        $version = $this->getVersionForUpdateOr404($this->request['config_version_id']);

        if (isset($this->request['id'])) {
            $number = $this->getNumberOr404($this->request['id']);
        } else {
            $number = Number::create($version);
        }

        $number->load($this->request, '');
        if ($number->type_id != 1) {
            $number->cpc_id = null;
        }
        $number->setPrefixlists($this->request['prefixlist_ids']);

        $transaction = Number::getDb()->beginTransaction();
        try {
            if (!$number->save()) {
                throw new FormValidationException($number);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }

    public function actionDelete()
    {
        $item = Number::findOne($this->request['id']);
        $this->getVersionForUpdateOr404($item->config_version_id);
        $item->delete();
    }
}
