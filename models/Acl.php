<?php

namespace app\models;

class Acl extends \yii\db\ActiveRecord
{
    const ITEM_TYPE_ACL = 2;
    
    public static function tableName()
    {
        return 'public.auth_item';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 100],
            [['description'], 'required'],
            [['description'], 'string', 'max' => 100],
            [['type'], 'integer']
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Название',
            'description' => 'Примечание',
        ];
    }

    public static function create(array $data = null)
    {
        $item = new self();
        $data['type'] = self::ITEM_TYPE_ACL;
        $item->load($data, '');
        return $item;
    }
    
    public static function find()
    {
        return parent::find()->where('type = ' . self::ITEM_TYPE_ACL);
    }
}