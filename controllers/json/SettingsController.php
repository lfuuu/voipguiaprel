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
            'name' => $server->name,
            'name_short' => $server->name_short,
            'emergency_prefixlist_id' => $server->emergency_prefixlist_id,
            'mcn_prefixlist_id' => $server->mcn_prefixlist_id,
            'h_call_sync_delay' => $server->h_call_sync_delay,
            'h_cdr_sync_delay' => $server->h_cdr_sync_delay,
            'h_call_save_delay' => $server->h_call_save_delay,
            'h_cdr_proc_wait_count' => $server->h_cdr_proc_wait_count,
            'h_call_save_wait_count' => $server->h_call_save_wait_count,
            'h_thread_error_count' => $server->h_thread_error_count,
            'h_radius_request_delay' => $server->h_radius_request_delay,
            'h_event_management' => $server->h_event_management,
            'h_local_events' => $server->h_local_events,
            'auto_lock_finance' => $server->instanceSettings->auto_lock_finance,
            'hostname_reserve' => $server->hostname_reserve,
            'nas_ip_address' => $server->nas_ip_address,
            'rc_mgmn_outcome_id' => $server->rc_mgmn_outcome_id,
            'is_sormed' => $server->is_sormed,
            'is_production' => $server->is_production,
            'vats_trunk_id' => $server->vats_trunk_id,
            'rc_mgmn_action_disable' => $server->rc_mgmn_action_disable,
            'ast_trunk_group_id' => $server->ast_trunk_group_id,
            'fsb_blacklist_id' => $server->fsb_blacklist_id
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
