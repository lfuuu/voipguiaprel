<?php

namespace app\models;
use app\queries\RouteTableQuery;

/**
 * @property int $id
 * @property string $name
 * @property int $server_id
 * @property
 */
class RouteTable extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.route_table';
    }

    public static function find()
    {
        return new RouteTableQuery(get_called_class());
    }

    public static function create(Server $server, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->server_id = $server->id;
        return $item;
    }

    public function rules()
    {
        return [
            [['sw_shared'], 'boolean'],
            [['name'], 'string', 'max' => 50],
        ];
    }
}