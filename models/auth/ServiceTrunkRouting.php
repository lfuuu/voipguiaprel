<?php

namespace app\models\auth;
use app\queries\auth\ServiceTrunkRoutingQuery;

/**
 * @property int $id
 * @property bool $uplink_enabled
 * @property string $trunk_groups
 */
class ServiceTrunkRouting extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.service_trunk_routing';
    }

    public static function find()
    {
        return new ServiceTrunkRoutingQuery(get_called_class());
    }

    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }

    public function rules()
    {
        return [
            [['trunk_groups'], 'string'],
            [['uplink_enabled'], 'boolean'],
        ];
    }
}