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
        $server = $this->getServerOr404($this->request['server_id']);

        return
            Number::find()
                ->select(['id', 'name', 'type_id'])
                ->where(['server_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $server = $this->getServerOr404($this->request['server_id']);

        return
            Number::find()
                ->select(['id', 'name', 'type_id', 'prefixlist_ids'])
                ->where(['server_id' => $server->id])
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
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            $number = $this->getNumberOr404($this->request['id']);
        } else {
            $number = Number::create($server);
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
        $item->delete();
    }
}
