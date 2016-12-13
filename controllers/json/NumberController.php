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
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;

        return
            Number::find()
                ->select(['id', 'name', 'type_id'])
                ->where("( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id)
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $server = $this->getServerOr404($this->request['server_id']);
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;

        return
            Number::find()
                ->select(['id', 'name', 'type_id', 'prefixlist_ids', 'show_in_stat','server_id','sw_shared'])
                ->where("( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id)
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
