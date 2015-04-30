<?php

namespace app\models;
use app\queries\TrunkQuery;

/**
 * @property int $id
 * @property int $server_id
 * @property int $code
 * @property string $name
 * @property bool $source_rule_default_allowed
 * @property bool $destination_rule_default_allowed
 * @property int $default_priority
 * @property string $trunk_name
 * @property bool $auto_routing
 * @property bool $our_trunk
 * @property int $route_table_id
 * @property
 */
class Trunk extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.trunk';
    }

    public static function find()
    {
        return new TrunkQuery(get_called_class());
    }

    public static function create(Server $server, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->server_id = $server->id;
        $item->default_priority = 0;
        return $item;
    }

    public function rules()
    {
        return [
            [['code'], 'integer', 'min'=> 1, 'max' => 99],
            [['name'], 'string', 'max' => 50],
            [['trunk_name','trunk_name_alias'], 'string', 'max' => 32],
            [['default_priority'], 'integer', 'min'=> -10, 'max' => 10],
            [['auto_routing', 'source_rule_default_allowed', 'destination_rule_default_allowed','our_trunk','auth_by_number'], 'boolean'],
            [['route_table_id'], 'integer'],
        ];
    }

    public function extraFields()
    {
        return ['routeTable', 'priorities', 'rules', 'numberPreprocessing'];
    }

    public function getRouteTable()
    {
        return $this->hasOne(RouteTable::className(), ['id' => 'route_table_id']);
    }

    public function getPriorities()
    {
        return $this->hasMany(TrunkPriority::className(), ['trunk_id' => 'id'])->orderBy('order');
    }

    public function getRules()
    {
        return $this->hasMany(TrunkRule::className(), ['trunk_id' => 'id'])->orderBy('order');
    }

    public function getNumberPreprocessing()
    {
        return $this->hasMany(TrunkNumberPreprocessing::className(), ['trunk_id' => 'id'])->orderBy('order');
    }
}