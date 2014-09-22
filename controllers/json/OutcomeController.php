<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\Outcome;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class OutcomeController extends JsonController
{
    public function actionList() {
        $version = $this->getVersionOr404($this->request['config_version_id']);

        return
            Outcome::find()
                ->select(['id', 'name'])
                ->where(['config_version_id' => $version->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $version = $this->getVersionOr404($this->request['config_version_id']);

        return
            Outcome::find()
                ->with('routeCase')
                ->with('releaseReason')
                ->with('airp')
                ->select(['id', 'name', 'type_id', 'route_case_id', 'release_reason_id', 'airp_id', 'calling_station_id', 'called_station_id'])
                ->where(['config_version_id' => $version->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        $item = Outcome::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'Outcome не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        $version = $this->getVersionForUpdateOr404($this->request['config_version_id']);

        if (isset($this->request['id'])) {
            $outcome = $this->getOutcomeOr404($this->request['id']);
        } else {
            $outcome = Outcome::create($version);
        }

        $outcome->load($this->request, '');

        $transaction = Outcome::getDb()->beginTransaction();
        try {
            if (!$outcome->save()) {
                throw new FormValidationException($outcome);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }

    public function actionDelete()
    {
        $item = Outcome::findOne($this->request['id']);
        $this->getVersionForUpdateOr404($item->config_version_id);
        $item->delete();
    }
}
