<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\Cpc;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class CpcController extends JsonController
{
    public function actionList() {
        $version = $this->getVersionOr404($this->request['config_version_id']);

        return
            Cpc::find()
                ->select(['id', 'name'])
                ->where(['config_version_id' => $version->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $version = $this->getVersionOr404($this->request['config_version_id']);

        return
            Cpc::find()
                ->select(['id', 'name'])
                ->where(['config_version_id' => $version->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        $item = Cpc::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'CPC не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        $version = $this->getVersionForUpdateOr404($this->request['config_version_id']);

        if (isset($this->request['id'])) {
            $item = $this->getAirpOr404($this->request['id']);
        } else {
            $item = Cpc::create($version);
        }

        $item->load($this->request, '');

        $transaction = Cpc::getDb()->beginTransaction();
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
        $item = Cpc::findOne($this->request['id']);
        $this->getVersionForUpdateOr404($item->config_version_id);
        $item->delete();
    }
}
