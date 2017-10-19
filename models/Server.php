<?php

namespace app\models;
use app\queries\ServerQuery;

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
 * @property string $nas_ip_address
 *
 * @property InstanceSettings $instanceSettings
 * @property string $apiUrl
 * @property string $apiUrlReserve
 *
 * @property int $mcn_prefixlist_id
 * @property int $vats_trunk_id
 *
 * @property int $instance_settings_id
 */
class Server extends \yii\db\ActiveRecord
{

    const API_DEFAULT_PORT = 8032;

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
            [['low_balance_outcome_id', 'blocked_outcome_id', 'hub_id', 'emergency_prefixlist_id', 'mcn_prefixlist_id','rc_mgmn_outcome_id','vats_trunk_id'], 'integer'],
            [['is_sormed', 'is_production','rc_mgmn_action_disable'], 'boolean'],

            [['calling_station_id_for_line_without_number'], 'string', 'max' => 100],
            [   [
                    'h_call_sync_delay', 'h_cdr_sync_delay', 'h_call_save_delay', 'h_cdr_proc_wait_count',
                    'h_call_save_wait_count', 'h_thread_error_count', 'h_radius_request_delay',
                    'h_event_management', 'h_local_events',
                ],
                'string',
                'max' => 100
            ],
            [['min_price_for_autorouting'], 'integer', 'min' => 1],
            [['service_numbers', 'hostname_reserve', 'nas_ip_address', 'name_short'], 'string'],
            [['hostname', 'name_short'], 'string', 'max' => 30],
        ];
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return 'http://' . $this->hostname . ':8032/';
    }

    /**
     * @return string
     */
    public function getApiUrlReserve()
    {
        return 'http://' . $this->hostname_reserve
            . (!parse_url($this->hostname_reserve, PHP_URL_PORT) ? ':' . self::API_DEFAULT_PORT : '')
            . '/';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInstanceSettings()
    {
        return $this->hasOne(InstanceSettings::className(), ['id' => 'id']);
    }


}