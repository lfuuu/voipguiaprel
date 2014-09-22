<?php

namespace app\controllers\json;

use Yii;
use app\models\ConfigVersion;
use app\classes\JsonController;
use app\exceptions\FormValidationException;

class SettingsController extends JsonController
{
    public function actionGet()
    {
        $version = $this->getVersionOr404($this->request['config_version_id']);

        return [
            'config_version_id' => $version->id,
            'name' => $version->name,
            'status_id' => $version->status_id,
            'low_balance_outcome_id' => $version->low_balance_outcome_id,
            'blocked_outcome_id' => $version->blocked_outcome_id,
            'cpc_routing_airp_id' => $version->cpc_routing_airp_id,
            'calling_station_id_for_line_without_number' => $version->calling_station_id_for_line_without_number,
            'export_chunk_size' => $version->export_chunk_size,
            'min_price_for_autorouting' => $version->min_price_for_autorouting,
        ];
    }

    public function actionSave()
    {
        $version = $this->getVersionForUpdateOr404($this->request['config_version_id']);

        $transaction = ConfigVersion::getDb()->beginTransaction();
        try {
            $version->load($this->request, '');

            if ($version->isAttributeChanged('min_price_for_autorouting')) {
                $version->need_recalc_routing_report = true;
            }

            if (!$version->save()) {
                throw new FormValidationException($version);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }

    public function actionDelete()
    {
        $item = $this->getVersionForUpdateOr404($this->request['config_version_id']);
        $item->delete();
    }
}
