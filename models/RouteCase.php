<?php

namespace app\models;
use app\queries\RouteCaseQuery;

/**
 * @property int $id
 * @property int $server_id
 * @property string $name
 * @property
 */
class RouteCase extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.route_case';
    }

    public static function find()
    {
        return new RouteCaseQuery(get_called_class());
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
            [['name'], 'string', 'min' => 4, 'max' => 50],
            [['name'], 'match', 'pattern' => '/^\w+$/'],
        ];
    }

    public function extraFields()
    {
        return ['operators'];
    }

    public function getTrunks()
    {
        return $this->hasMany(RouteCaseTrunk::className(), ['route_case_id' => 'id'])->orderBy('priority');
    }

}