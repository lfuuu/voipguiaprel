<?php

namespace app\models;

class Role extends \yii\db\ActiveRecord
{
    const ITEM_TYPE_ROLE = 1;
    
    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->type = self::ITEM_TYPE_ROLE;
    }
    
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
        $data['type'] = self::ITEM_TYPE_ROLE;
        $item->load($data, '');
        return $item;
    }
    
    public static function find()
    {
        return parent::find()->where('type = ' . self::ITEM_TYPE_ROLE);
    }

}