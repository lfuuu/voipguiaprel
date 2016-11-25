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
 *
 * @property string $apiUrl
 * @property
 */
class Server extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'public.server';
    }

    public static function find()
    {
        return new ServerQuery(get_called_class());
    }

    public function rules()
    {
        return [
            [['low_balance_outcome_id', 'blocked_outcome_id', 'hub_id', 'emergency_prefixlist_id'], 'integer'],
            [['calling_station_id_for_line_without_number'], 'string', 'max' => 100],
            [['min_price_for_autorouting'], 'integer', 'min' => 1],
            [['service_numbers'], 'string'],
            [['hostname'], 'string', 'max' => 30],
        ];
    }

    public function getApiUrl()
    {
        return'http://' . $this->hostname . ':8032/';
    }

    public function getInstanceSettings()
    {
        return $this->hasOne(InstanceSettings::className(), ['id' => 'id']);
    }


}