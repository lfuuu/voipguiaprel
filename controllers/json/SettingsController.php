<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use app\models\auth\Hub;
use app\models\auth\MvnoLink;
use app\models\Server;
use app\models\auth\CormServerRule;
use app\models\auth\DvoServerRule;
use yii\web\NotFoundHttpException;

class SettingsController extends JsonController
{
    public function actionGet()
    {
        if (!\Yii::$app->user->can('general_settings_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);
        $hub = Hub::findOne($server->hub_id);
        
        $trunkRulesOrigination = CormServerRule::findAll(['server_id' => $server->id, 'is_orig' => true]);
        $trunkRulesTermination = CormServerRule::findAll(['server_id' => $server->id, 'is_orig' => false]);
        $dvoRules = DvoServerRule::findAll(['server_id' => $server->id]);

        $hubNumberCapacityFormatted = [];
        $hubExcludedNumberCapacityFormatted = [];
        $prefixlistBlock = [];
        $trunkGroups = '';
        
        if (isset($hub)) {
            $hubNumberCapacityFormatted = (!is_null($hub->number_capacity) ? explode(',', str_replace(['{', '}'], '', $hub->number_capacity)) : []);
            $hubExcludedNumberCapacityFormatted = (!is_null($hub->excluded_number_capacity) ? explode(',', str_replace(['{', '}'], '', $hub->excluded_number_capacity)) : []);
            
            $trunkGroups = $hub->trunk_groups;
        }
    
        if ($server->prefixlist_block && $server->prefixlist_block !== '{}') {
            $prefixlistBlock = explode(',', str_replace(['{', '}'], '', $server->prefixlist_block));
        }
        
        $mvnoLinkList = MvnoLink::findAll(['server_id' => $server->id]);

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
            'trunkRulesOrigination' => $trunkRulesOrigination,
            'trunkRulesTermination' => $trunkRulesTermination,
            'corm_orig' => $server->corm_orig,
            'corm_term' => $server->corm_term,
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
            'loop_detected_outcome_id' => $server->loop_detected_outcome_id,
            'reject_outcome_id' => $server->reject_outcome_id,
            'phase1_allow_trunkgroup_id' => $server->phase1_allow_trunkgroup_id,
            'hub_id' => $server->hub_id,
            'hub_id' => $server->hub_id,
            'prefixlist_block' => $prefixlistBlock,
            'hub_number_capacity' => $hubNumberCapacityFormatted,
            'hub_excluded_number_capacity' => $hubExcludedNumberCapacityFormatted,
            'trunk_groups' => $trunkGroups,
            'rn_replace_prefixlist_id' => $server->rn_replace_prefixlist_id,
            'verification_b_in_nnp_prefixlist_id' => $server->verification_b_in_nnp_prefixlist_id,
            'fsb_numa_blacklist_ids' => $server->fsb_numa_blacklist_ids,
            'fsb_numb_blacklist_ids' => $server->fsb_numb_blacklist_ids,
            'local_spc' => $server->local_spc,
            'zone_spc' => $server->zone_spc,
            'mg_spc' => $server->mg_spc,
            'mn_spc' => $server->mn_spc,
            'mvno_link' => $this->readMvnoLink($mvnoLinkList),
            'mts_timeout' => $server->mts_timeout,
            'mts_error' => $server->mts_error,
            'mts_reject' => $server->mts_reject,
            'epvv_timeout' => $server->epvv_timeout,
            'epvv_error' => $server->epvv_error,
            'epvv_reject' => $server->epvv_reject,
            'route_case_not_found_nnp' => $server->route_case_not_found_nnp,
            'route_case_not_found_pricelist' => $server->route_case_not_found_pricelist,
            'dvoRules' => $this->formatDvoRules($dvoRules),
            'call_telemetry_receiver_orig' => $server->call_telemetry_receiver_orig,
            'call_telemetry_receiver_term' => $server->call_telemetry_receiver_term,
            'dvo_telemetry_receiver' => $server->dvo_telemetry_receiver,
            'dvo_enable_default' => $server->dvo_enable_default,
        ];
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('general_settings_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);
        $hub = Hub::findOne($server->hub_id);

        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            if (isset($this->request['prefixlist_block'])) {
                $prefixlistBlock = $this->request['prefixlist_block'];
                unset($this->request['prefixlist_block']);
            }

            if (isset($this->request['fsb_numa_blacklist_ids'])) {
                $fsbNumaBlacklistIds = $this->request['fsb_numa_blacklist_ids'];
                unset($this->request['fsb_numa_blacklist_ids']);
            }

            if (isset($this->request['fsb_numb_blacklist_ids'])) {
                $fsbNumbBlacklistIds = $this->request['fsb_numb_blacklist_ids'];
                unset($this->request['fsb_numb_blacklist_ids']);
            }
            
            if (isset($this->request['mvno_link'])) {
                $mvnoLinkList = $this->request['mvno_link'];
                unset($this->request['mvno_link']);
            }

            $server->load($this->request, '');
            
            if ($prefixlistBlock) {
                $server->prefixlist_block = '{' . implode(',', $prefixlistBlock) . '}';
            }

            if ($fsbNumaBlacklistIds) {
                $server->fsb_numa_blacklist_ids = '{' . implode(',', $fsbNumaBlacklistIds) . '}';
            }

            if ($fsbNumbBlacklistIds) {
                $server->fsb_numb_blacklist_ids = '{' . implode(',', $fsbNumbBlacklistIds) . '}';
            }
            
            if ($mvnoLinkList) {
                $this->saveMvnoLink($mvnoLinkList, $this->request['server_id']);
            } else {
                MvnoLink::deleteAll(['server_id' => $this->request['server_id']]);
            }

            if ($server->isAttributeChanged('min_price_for_autorouting')) {
                $server->need_recalc_routing_report = true;
            }
            
            if (isset($hub)) {
                if (isset($this->request['hub_number_capacity'])) {
                    $hub->number_capacity = (!empty($this->request['hub_number_capacity']) ? '{' . implode(',', $this->request['hub_number_capacity']) . '}' : null);
                }
                
                if (isset($this->request['hub_excluded_number_capacity'])) {
                    $hub->excluded_number_capacity = (!empty($this->request['hub_excluded_number_capacity']) ? '{' . implode(',', $this->request['hub_excluded_number_capacity']) . '}' : null);
                }
    
                if (isset($this->request['trunk_groups'])) {
                    $hub->trunk_groups = '{' . implode(',', $this->request['trunk_groups']) . '}';
                }
            }
    
            if (!$server->save() || (isset($hub) && !$hub->save())) {
                throw new FormValidationException($server);
            }

            CormServerRule::deleteByServer($server);
            DvoServerRule::deleteByServer($server);

            if (isset($this->request['trunkRulesOrigination'])) {
                foreach ($this->request['trunkRulesOrigination'] as $ruleData) {
                    $rule = new CormServerRule();
                    $rule->load($ruleData, '');
                    $rule->ac_mode = isset($ruleData['ac_mode']) && $ruleData['ac_mode'] === 1 ? 1 : 0;
                    $rule->server_id = $server->id;
                    $rule->is_orig = true;
                    if (!$rule->save()) {
                        throw new FormValidationException($rule);
                    }
                }
            }

            if (isset($this->request['dvoRules'])) {
                foreach ($this->request['dvoRules'] as $ruleData) {
                    $rule = new DvoServerRule();
                    $rule->load($ruleData, '');
                    $rule->server_id = $server->id;
                    if (!$rule->save()) {
                        throw new FormValidationException($rule);
                    }
                }
            }
    
            if (isset($this->request['trunkRulesTermination'])) {
                foreach ($this->request['trunkRulesTermination'] as $ruleData) {
                    $rule = new CormServerRule();
                    $rule->load($ruleData, '');
                    $rule->ac_mode = isset($ruleData['ac_mode']) && $ruleData['ac_mode'] === 1 ? 1 : 0;
                    $rule->server_id = $server->id;
                    $rule->is_orig = false;
                    if (!$rule->save()) {
                        throw new FormValidationException($rule);
                    }
                }
            }

            if (isset($this->request['corm_term'])) {
                $server->corm_term = $this->request['corm_term'];
            } else {
                $server->corm_term = false;
            }
            
            if (isset($this->request['corm_orig'])) {
                $server->corm_orig = $this->request['corm_orig'];
            } else {
                $server->corm_orig = false;
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }
    
    private function saveMvnoLink($mvnoLinkList, $serverId)
    {
        MvnoLink::deleteAll(['server_id' => $serverId]);
        
        foreach ($mvnoLinkList as $mvnoLink) {
            $params = [
                'server_id' => $serverId,
                'mvno_partner_id' => $mvnoLink['mvno_partner_id'],
                'mvno_trunk_ids' => (!empty($mvnoLink['mvno_trunk_ids']) ? '{' . implode(',', $mvnoLink['mvno_trunk_ids']) . '}' : null),
                'trunk_groups' => (!empty($mvnoLink['trunk_groups']) ? '{' . implode(',', $mvnoLink['trunk_groups']) . '}' : null),
                'number_capacity' => (!empty($mvnoLink['number_capacity']) ? '{' . implode(',', $mvnoLink['number_capacity']) . '}' : null),
                'ported_number_prefixes' => (!empty($mvnoLink['ported_number_prefixes']) ? '{' . implode(',', $mvnoLink['ported_number_prefixes']) . '}' : null),
                'excluded_number_prefixes' => (!empty($mvnoLink['excluded_number_prefixes']) ? '{' . implode(',', $mvnoLink['excluded_number_prefixes']) . '}' : null),
                'routing_number' => $mvnoLink['routing_number']
            ];
            
            $mvnoLinkObject = MvnoLink::create($params);
            $mvnoLinkObject->save();
        }
    }
    
    private function readMvnoLink($mvnoLinkList)
    {
        $result = [];
        
        foreach ($mvnoLinkList as $mvnoLink) {
            $result[] = [
                'server_id' => $mvnoLink->server_id,
                'mvno_partner_id' => $mvnoLink->mvno_partner_id,
                'mvno_trunk_ids' => (!is_null($mvnoLink->mvno_trunk_ids) ? explode(',', str_replace(['{', '}'], '', $mvnoLink->mvno_trunk_ids)) : []),
                'trunk_groups' => (!is_null($mvnoLink->trunk_groups) ? explode(',', str_replace(['{', '}'], '', $mvnoLink->trunk_groups)) : []),
                'number_capacity' => (!is_null($mvnoLink->number_capacity) ? explode(',', str_replace(['{', '}'], '', $mvnoLink->number_capacity)) : []),
                'ported_number_prefixes' => (!is_null($mvnoLink->ported_number_prefixes) ? explode(',', str_replace(['{', '}'], '', $mvnoLink->ported_number_prefixes)) : []),
                'excluded_number_prefixes' => (!is_null($mvnoLink->excluded_number_prefixes) ? explode(',', str_replace(['{', '}'], '', $mvnoLink->excluded_number_prefixes)) : []),
                'routing_number' => $mvnoLink->routing_number
            ];
        }
        
        return $result;
    }
    
    public function actionGetNasIpAddress()
{
    if (!\Yii::$app->user->can('test_number_edit')) {
        throw new ForbiddenHttpException('Access denied');
    }

    $serverId = Yii::$app->request->getBodyParam('server_id');

    if ($serverId === null) {
        throw new BadRequestHttpException('Server ID not provided');
    }

    // Try to find Server with the given server_id
    $server = Server::findOne($serverId);

    // Check if Server with the given server_id exists
    if ($server === null) {
        throw new NotFoundHttpException('Server not found for the provided ID');
    }

    // Retrieve nas_ip_address from Server
    $nasIpAddress = $server->nas_ip_address;

    return [
        'nas_ip_address' => $nasIpAddress,
    ];
}
private function formatDvoRules($dvoRules)
{
    $result = [];
    foreach ($dvoRules as $rule) {
        $result[] = [
            'id' => $rule->id,
            'allow' => $rule->allow,
            'number_id_filter_a' => $rule->number_id_filter_a,
            'telemetry_receiver_id' => $rule->telemetry_receiver_id,
            'object_comment' => $rule->object_comment,
            'order' => $rule->order,
        ];
    }
    return $result;
}


}
