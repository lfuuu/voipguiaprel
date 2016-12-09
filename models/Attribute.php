<?php

namespace app\models;

use app\queries\AttributeQuery;

class Attribute extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.atribute';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['note'], 'string'],
            [['name'], 'string', 'max' => 50]
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'Ключ',
            'name' => 'Название',
            'note' => 'Примечание',
        ];
    }

}