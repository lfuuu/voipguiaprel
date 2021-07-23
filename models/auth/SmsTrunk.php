<?php

namespace app\models\auth;
use app\queries\auth\SmsTrunkQuery;

/**
 * @property int $id
 * @property string $name
 * @property int $server_id
 * @property int $a2psms_route_table_id
 * @property string $route_name
 */
class SmsTrunk extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.a2psms_route';
    }

    public static function find()
    {
        return new SmsTrunkQuery(get_called_class());
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
            [['name', 'route_name'], 'string'],
            [['id', 'server_id', 'a2psms_route_table_id'], 'integer'],
        ];
    }
}