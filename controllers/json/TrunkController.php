<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\Trunk;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class TrunkController extends JsonController
{
    public function actionList() {
        $version = $this->getVersionOr404($this->request['config_version_id']);

        return
            Trunk::find()
                ->select(['id', 'name'])
                ->where(['config_version_id' => $version->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $version = $this->getVersionOr404($this->request['config_version_id']);

        return
            Trunk::find()
                ->with('routeTable')
                ->select(['id', 'name', 'number', 'route_table_id'])
                ->where(['config_version_id' => $version->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        $item = Trunk::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'Группа транков не найдена');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        $version = $this->getVersionForUpdateOr404($this->request['config_version_id']);

        if (isset($this->request['id'])) {
            $trunk = $this->getTrunkOr404($this->request['id']);
        } else {
            $trunk = Trunk::create($version);
        }

        $trunk->load($this->request, '');

        $transaction = Trunk::getDb()->beginTransaction();
        try {
            if (!$trunk->save()) {
                throw new FormValidationException($trunk);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }

    public function actionDelete()
    {
        $item = Trunk::findOne($this->request['id']);
        $this->getVersionForUpdateOr404($item->config_version_id);
        $item->delete();
    }
}
