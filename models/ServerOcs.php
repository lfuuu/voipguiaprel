<?php

namespace app\models;

use app\models\auth\CamelGtNumberPreprocessing;
use app\queries\ServerOcsQuery;

/**
 * @property int $id
 * @property string $name
 * @property bool $active
 * @property string $type
 * @property string $camel_gw
 * @property string $camel_reserve
 * @property int $default_routing_server_id
 */
class ServerOcs extends \yii\db\ActiveRecord
{
    public $_subitems = [
        'numberPreprocessing' => 'getNumberPreprocessing'
    ];

    public static function tableName()
    {
        return 'public.server_ocs';
    }

    public static function find()
    {
        return new ServerOcsQuery(get_called_class());
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
            [['name', 'type', 'camel_gw', 'camel_reserve'], 'string'],
            [['default_routing_server_id'], 'integer'],
            [['active'], 'boolean']
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getNumberPreprocessing()
    {
        return $this->hasMany(CamelGtNumberPreprocessing::className(), ['camel_server_id' => 'id'])->orderBy('order');
    }
}