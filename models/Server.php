<?php

namespace app\models;
use app\queries\ServerQuery;
use yii\db\Expression;
use app\models\event\Queue;

/**
 * @property int $id
 * @property string $name
 * @property int $low_balance_outcome_id
 * @property int $blocked_outcome_id
 * @property string $calling_station_id_for_line_without_number
 * @property bool $need_recalc_routing_report
 * @property int $min_price_for_autorouting
 * @property int $our_numbers_id
 * @property int $hostname
 * @property string $service_numbers
 * @property int $hub_id
 * @property int $emergency_prefixlist_id
 * @property string $h_call_sync_delay
 * @property string $h_cdr_sync_delay
 * @property string $h_call_save_delay
 * @property string $h_cdr_proc_wait_count
 * @property string $h_call_save_wait_count
 * @property string $h_thread_error_count
 * @property string $h_radius_request_delay
 * @property string $h_event_management
 * @property string $h_local_events
 * @property bool $is_need_db_do_migrate
 * @property bool $is_production
 * @property string $hostname_reserve
 * @property string $hostname_reserve_2
 * @property string $hostname_dev
 * @property string $nas_ip_address
 * @property int $ast_trunk_group_id
 * @property int $fsb_blacklist_id
 * @property int $fsb_b_blacklist_id
 * @property int $global_replacement_id
 * @property bool $is_route_to_class5
 * @property bool $is_route_to_class5_phase1_enable
 * @property int $number_id_filter_b_route_to_class5
 * @property int $cpc_id
 * @property bool $is_autotest_error_enabled
 * @property bool $is_open_numeric_plan_enabled
 * @property int $loop_detected_outcome_id
 * @property int $phase1_allow_trunkgroup_id
 * @property string $prefixlist_block
 * @property int $rn_replace_prefixlist_id
 * @property int $fsb_numa_blacklist_ids
 * @property int $fsb_numb_blacklist_ids
 *
 * @property InstanceSettings $instanceSettings
 * @property string $apiUrl
 * @property string $apiUrlReserve
 * @property string $apiUrlReserve2
 * @property string $apiUrlDev
 * @property bool $syncInProgress
 *
 * @property int $mcn_prefixlist_id
 * @property int $vats_trunk_id
 *
 * @property int $instance_settings_id
 * 
 * @property string $local_spc
 * @property string $zone_spc
 * @property string $mg_spc
 * @property string $mn_spc
 */
class Server extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'public.server';
    }

    /**
     * @return ServerQuery
     */
    public static function find()
    {
        return new ServerQuery(get_called_class());
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['low_balance_outcome_id', 'blocked_outcome_id', 'hub_id', 'emergency_prefixlist_id',
                'mcn_prefixlist_id','rc_mgmn_outcome_id','vats_trunk_id', 'ast_trunk_group_id',
                'fsb_blacklist_id', 'fsb_b_blacklist_id', 'global_replacement_id', 'number_id_filter_b_route_to_class5',
                'cpc_id', 'loop_detected_outcome_id', 'phase1_allow_trunkgroup_id', 'rn_replace_prefixlist_id'], 'integer'],
            [['is_sormed', 'is_production','rc_mgmn_action_disable','is_route_to_class5',
                'is_route_to_class5_phase1_enable','is_autotest_error_enabled', 'is_open_numeric_plan_enabled'], 'boolean'],
            [['calling_station_id_for_line_without_number',
                'h_call_sync_delay', 'h_cdr_sync_delay', 'h_call_save_delay', 'h_cdr_proc_wait_count',
                'h_call_save_wait_count', 'h_thread_error_count', 'h_radius_request_delay',
                'h_event_management', 'h_local_events'], 'string', 'max' => 100],
            [['min_price_for_autorouting'], 'integer', 'min' => 1],
            [['service_numbers', 'hostname_reserve', 'hostname_reserve_2', 'hostname_dev',
                'nas_ip_address', 'name_short', 'prefixlist_block', 'fsb_numa_blacklist_ids', 'fsb_numb_blacklist_ids'], 'string'],
            [['hostname', 'name_short', 'name'], 'string', 'max' => 30],
            [['local_spc', 'zone_spc', 'mg_spc', 'mn_spc'], 'string', 'max' => 32],
        ];
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->hostname;
    }

    /**
     * @return string
     */
    public function getApiUrlReserve()
    {
        return $this->hostname_reserve;
    }
    
    /**
     * @return string
     */
    public function getApiUrlReserve2()
    {
        return $this->hostname_reserve_2;
    }
    
    /**
     * @return string
     */
    public function getApiUrlDev()
    {
        return $this->hostname_dev;
    }
    
    /**
     * @return bool
     */
    public function getSyncInProgress()
    {
        if (!$this->hub_id) {
            $where = "server_id = " . $this->id;
        } else {
            $where = "(server_id in (select id from public.server where hub_id = " . $this->hub_id . ")) or server_id = " . $this->id;
        }
        
        $result = Queue::find()
            ->where($where)
            ->exists();
        
        return $result;
    }

    public function getMinServerOnHub()
    {
        if (!$this->hub_id) {
            $result = $this->id;
        } else {
            $result = self::find()
                ->select(['id' => new Expression("min(id)")])
                ->where(['hub_id' => $this->hub_id])
                ->asArray()
                ->one();
        }
        
        return $result;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInstanceSettings()
    {
        return $this->hasOne(InstanceSettings::className(), ['id' => 'id']);
    }

    public function getPreparedPrefixlists()
    {
        return $this->hasMany(Prefixlist::className(), ['server_id' => 'id'])
            ->onCondition('dt_prepare is not null');
    }
}