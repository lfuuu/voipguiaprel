<?php

namespace app\models;

class UserAcl extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'public.auth_assignment';
    }

    public function rules()
    {
        return [
            [['item_name', 'user_id'], 'required'],
            [['item_name'], 'string', 'max' => 100],
            [['user_id'], 'required'],
            [['user_id'], 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'item_name' => 'Роль',
            'user_id' => 'Идентификатор пользователя',
        ];
    }

    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDescription()
    {
        return $this->hasOne(Role::className(), ['name' => 'item_name']);
    }
}