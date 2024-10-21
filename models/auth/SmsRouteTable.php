<?php

namespace app\models\auth;
use app\queries\auth\SmsRouteTableQuery;

/**
 * @property int $id
 * @property string $name
 * @property string $object_comment
 * @property int $server_id
 * @property string $route_mode
 */
class SmsRouteTable extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.a2psms_route_table';
    }

    public static function find()
    {
        return new SmsRouteTableQuery(get_called_class());
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
            [['server_id', 'id'], 'integer'],
            [['route_mode'], 'string', 'max' => 15],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoutes()
    {
        return $this->hasMany(SmsRouteTableRoute::className(), ['a2psms_route_table_id' => 'id'])->orderBy('order');
    }
}
