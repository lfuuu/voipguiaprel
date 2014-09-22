<?php

namespace app\models;
use app\dao\ConfigVersionDao;
use app\queries\ConfigVersionQuery;

/**
 * @property int $id
 * @property int $server_id
 * @property string $name
 * @property int $status_id
 * @property string $updated_at
 * @property string $activated_at
 * @property int $low_balance_outcome_id
 * @property int $blocked_outcome_id
 * @property string $calling_station_id_for_line_without_number
 * @property int $export_chunk_size
 * @property int $cpc_routing_airp_id
 * @property bool $need_recalc_routing_report
 * @property int $min_price_for_autorouting
 * @property
 */
class ConfigVersion extends \yii\db\ActiveRecord
{
    const STATUS_DRAFT = 1;
    const STATUS_PUBLISHED = 2;
    const STATUS_ACTIVE = 3;

    public static function tableName()
    {
        return 'auth.config_version';
    }

    public static function find()
    {
        return new ConfigVersionQuery(get_called_class());
    }

    public static function dao()
    {
        return ConfigVersionDao::me();
    }

    public function getServer()
    {
        return $this->hasOne(Server::className(), ['id' => 'server_id']);
    }

    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 200],
            [['low_balance_outcome_id', 'blocked_outcome_id', 'cpc_routing_airp_id'], 'integer'],
            [['calling_station_id_for_line_without_number'], 'string', 'max' => 100],
            [['export_chunk_size'], 'integer', 'min' => 2, 'max' => 30000],
            [['min_price_for_autorouting'], 'integer', 'min' => 1],
        ];
    }

    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        $data = parent::toArray($fields, $expand, $recursive);
        $data['editable'] = true;//$this->status_id == self::STATUS_DRAFT;
        return $data;
    }

}