<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;

class SettingsController extends JsonController
{
    public function actionGet()
    {
        $server = $this->getServerOr404($this->request['server_id']);
        

        return [
            'server_id' => $server->id,
            'low_balance_outcome_id' => $server->low_balance_outcome_id,
            'blocked_outcome_id' => $server->blocked_outcome_id,
            'calling_station_id_for_line_without_number' => $server->calling_station_id_for_line_without_number,
            'min_price_for_autorouting' => $server->min_price_for_autorouting,
            'service_numbers' => $server->service_numbers,
            'hostname' => $server->hostname,
        ];
    }

    public function actionSave()
    {
        $server = $this->getServerOr404($this->request['server_id']);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $server->load($this->request, '');

            if ($server->isAttributeChanged('min_price_for_autorouting')) {
                $server->need_recalc_routing_report = true;
            }

            if (!$server->save()) {
                throw new FormValidationException($server);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }
}
