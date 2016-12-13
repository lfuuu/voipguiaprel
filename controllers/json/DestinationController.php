<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\Destination;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class DestinationController extends JsonController
{
    public function actionList() {
        $server = $this->getServerOr404($this->request['server_id']);

        return
            Destination::find()
                ->select(['id', 'name'])
                ->where(['server_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $server = $this->getServerOr404($this->request['server_id']);

        return
            Destination::find()
                ->select(['id', 'name', 'prefixlist_ids'])
                ->where(['server_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        $item = Destination::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'Номер не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            $destination = $this->getDestinationOr404($this->request['id']);
        } else {
            $destination = Destination::create($server);
        }

        $destination->load($this->request, '');
        $destination->setPrefixlists($this->request['prefixlist_ids']);

        $transaction = Destination::getDb()->beginTransaction();
        try {
            if (!$destination->save()) {
                throw new FormValidationException($destination);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }

    public function actionDelete()
    {
        $item = Destination::findOne($this->request['id']);
        $item->delete();
    }
}
