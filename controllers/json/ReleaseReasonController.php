<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\ReleaseReason;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class ReleaseReasonController extends JsonController
{
    public function actionList() {
        $version = $this->getVersionOr404($this->request['config_version_id']);

        return
            ReleaseReason::find()
                ->select(['id', 'name'])
                ->where(['config_version_id' => $version->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $version = $this->getVersionOr404($this->request['config_version_id']);

        return
            ReleaseReason::find()
                ->select(['id', 'name'])
                ->where(['config_version_id' => $version->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        $item = ReleaseReason::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'Release reason не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        $version = $this->getVersionForUpdateOr404($this->request['config_version_id']);

        if (isset($this->request['id'])) {
            $releaseReason = $this->getReleaseReasonOr404($this->request['id']);
        } else {
            $releaseReason = ReleaseReason::create($version);
        }

        $releaseReason->load($this->request, '');

        $transaction = ReleaseReason::getDb()->beginTransaction();
        try {
            if (!$releaseReason->save()) {
                throw new FormValidationException($releaseReason);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }

    public function actionDelete()
    {
        $item = ReleaseReason::findOne($this->request['id']);
        $this->getVersionForUpdateOr404($item->config_version_id);
        $item->delete();
    }
}
