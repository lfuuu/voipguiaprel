<?php

namespace app\models;
use app\queries\UvrGroupQuery;

/**
 * @property int $id
 * @property string $name
 */
class UvrGroup extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.uvr_group';
    }

    public static function find()
    {
        return new UvrGroupQuery(get_called_class());
    }

    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 50],
        ];
    }
}