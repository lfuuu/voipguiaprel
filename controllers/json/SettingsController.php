<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;

class SettingsController extends JsonController
{
    public function actionGet()
    {
        if (!\Yii::$app->user->can('general_settings_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
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
            'hostname_reserve_2' => $server->hostname_reserve_2,
            'hostname_dev' => $server->hostname_dev,
            'nas_ip_address' => $server->nas_ip_address,
            'rc_mgmn_outcome_id' => $server->rc_mgmn_outcome_id,
            'is_sormed' => $server->is_sormed,
            'is_production' => $server->is_production,
            'vats_trunk_id' => $server->vats_trunk_id,
            'rc_mgmn_action_disable' => $server->rc_mgmn_action_disable,
            'ast_trunk_group_id' => $server->ast_trunk_group_id,
            'fsb_blacklist_id' => $server->fsb_blacklist_id,
            'fsb_b_blacklist_id' => $server->fsb_b_blacklist_id,
            'global_replacement_id' => $server->global_replacement_id,
            'is_route_to_class5' => $server->is_route_to_class5,
            'is_route_to_class5_phase1_enable' => $server->is_route_to_class5_phase1_enable,
            'number_id_filter_b_route_to_class5' => $server->number_id_filter_b_route_to_class5,
            'cpc_id' => $server->cpc_id,
            'is_autotest_error_enabled' => $server->is_autotest_error_enabled,
            'is_open_numeric_plan_enabled' => $server->is_open_numeric_plan_enabled,
            'loop_detected_outcome_id' => $server->loop_detected_outcome_id
        ];
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('general_settings_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
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
