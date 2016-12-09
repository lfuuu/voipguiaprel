<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\Airp;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class AirpController extends JsonController
{
    public function actionList() {
        $server = $this->getServerOr404($this->request['server_id']);

        return
            Airp::find()
                ->select(['id', 'name'])
                ->where(['server_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $server = $this->getServerOr404($this->request['server_id']);

        return
            Airp::find()
                ->select(['id', 'name'])
                ->where(['server_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        $item = Airp::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'AIRP не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            $item = $this->getAirpOr404($this->request['id']);
        } else {
            $item = Airp::create($server);
        }

        $item->load($this->request, '');

        $transaction = Airp::getDb()->beginTransaction();
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
        $item = Airp::findOne($this->request['id']);
        $item->delete();
    }
}
