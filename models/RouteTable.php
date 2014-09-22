<?php

namespace app\models;
use app\queries\RouteTableQuery;

/**
 * @property int $id
 * @property string $name
 * @property string $config_version_id
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

    public static function create(ConfigVersion $version, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->config_version_id = $version->id;
        return $item;
    }

    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 50],
        ];
    }
}