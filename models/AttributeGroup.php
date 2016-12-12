<?php

namespace app\models;

use app\queries\AttributeGroupQuery;

class AttributeGroup extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return 'auth.attribute_group';
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

    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }


}