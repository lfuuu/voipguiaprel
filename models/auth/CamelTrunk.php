<?php

namespace app\models\auth;
use app\queries\auth\CamelTrunkQuery;

/**
 * @property int $id
 * @property string $name
 * @property int $camel_route_table_id
 * @property int $server_id
 * @property int $prefixlist_id
 */
class CamelTrunk extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.camel_trunk';
    }

    public static function find()
    {
        return new CamelTrunkQuery(get_called_class());
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
            [['name'], 'string'],
            [['prefixlist_id', 'camel_route_table_id', 'server_id'], 'integer']
        ];
    }
}