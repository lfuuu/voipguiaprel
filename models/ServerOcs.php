<?php

namespace app\models;
use app\queries\ServerOcsQuery;

/**
 * @property int $id
 * @property string $name
 * @property bool $active
 * @property string $type
 * @property string $camel_gw
 */
class ServerOcs extends \yii\db\ActiveRecord
{
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
            [['name', 'type', 'camel_gw'], 'string'],
            [['active'], 'boolean']
        ];
    }
}