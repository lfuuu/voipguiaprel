<?php

namespace app\models;
use app\queries\AirpQuery;

/**
 * @property int $id
 * @property int $server_id
 * @property string $name
 * @property string $object_comment
 * @property
 */
class Airp extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.airp';
    }

    public static function find()
    {
        return new AirpQuery(get_called_class());
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
            [['name'], 'string', 'max' => 50],
            [['object_comment'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
        ];
    }
}