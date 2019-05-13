<?php

namespace app\models;
use app\queries\RouteTableQuery;

/**
 * @property int $id
 * @property string $name
 * @property int $server_id
 * @property string $object_comment
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
            [['object_comment'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRouteRules()
    {
        return $this->hasMany(RouteRouteRule::className(), ['route_table_id' => 'id'])->orderBy('order');
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRoutes()
    {
        return $this->hasMany(RouteTableRoute::className(), ['route_table_id' => 'id'])->orderBy('order');
    }
}