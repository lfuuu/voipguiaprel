<?php

namespace app\models;

class RoleAcl extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'public.auth_item_child';
    }

    public function rules()
    {
        return [
            [['parent'], 'required'],
            [['child'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'parent' => 'Родитель',
            'child' => 'Наследник',
        ];
    }

    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }

}