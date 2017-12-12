<?php

namespace app\models;
use app\queries\CpcQuery;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property
 */
class Cpc extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.cpc';
    }

    public static function find()
    {
        return new CpcQuery(get_called_class());
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
            [['name', 'description'], 'string'],
        ];
    }
}