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
        $server = $this->getServerOr404($this->request['server_id']);
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;

        return
            Outcome::find()
                ->select(['id', 'name'])
                ->where("( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id)
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $server = $this->getServerOr404($this->request['server_id']);
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;

        return
            Outcome::find()
                ->with('routeCase')
                ->with('releaseReason')
                ->with('airp')
                ->select(['id', 'name', 'type_id', 'route_case_id', 'release_reason_id', 'airp_id', 'calling_station_id', 'called_station_id','server_id','sw_shared'])
                ->where("( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id)
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
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            $outcome = $this->getOutcomeOr404($this->request['id']);
        } else {
            $outcome = Outcome::create($server);
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
        $item->delete();
    }
}
