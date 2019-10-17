<?php

namespace app\models\auth;
use app\queries\auth\CamelRouteTableQuery;

/**
 * @property int $id
 * @property string $name
 * @property string $object_comment
 */
class CamelRouteTable extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.camel_route_table';
    }

    public static function find()
    {
        return new CamelRouteTableQuery(get_called_class());
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
            [['name', 'object_comment'], 'string'],
        ];
    }
}