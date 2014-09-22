<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\TrunkGroup;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class TrunkGroupController extends JsonController
{
    public function actionList() {
        $version = $this->getVersionOr404($this->request['config_version_id']);

        return
            TrunkGroup::find()
                ->select(['id', 'name'])
                ->where(['config_version_id' => $version->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $version = $this->getVersionOr404($this->request['config_version_id']);

        return
            TrunkGroup::find()
                ->select(['id', 'name'])
                ->where(['config_version_id' => $version->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        $item = TrunkGroup::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'Группа транков не найдена');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        $version = $this->getVersionForUpdateOr404($this->request['config_version_id']);

        if (isset($this->request['id'])) {
            $number = $this->getTrunkGroupOr404($this->request['id']);
        } else {
            $number = TrunkGroup::create($version);
        }

        $number->load($this->request, '');
        $number->setTrunks($this->request['trunk_ids']);

        $transaction = TrunkGroup::getDb()->beginTransaction();
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
        $item = TrunkGroup::findOne($this->request['id']);
        $this->getVersionForUpdateOr404($item->config_version_id);
        $item->delete();
    }
}
