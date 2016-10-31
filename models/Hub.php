<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "auth.hub".
 *
 * @property integer $id
 * @property string $dt
 * @property string $name
 * @property string $note
 */
class Hub extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth.hub';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dt'], 'safe'],
            [['name'], 'required'],
            [['note'], 'string'],
            [['name'], 'string', 'max' => 50]
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Ключ',
            'dt' => 'Время',
            'name' => 'Название',
            'note' => 'Примечание',
        ];
    }

    public function getServers()
    {
        return $this->hasMany(Server::className(), ['hub_id' => 'id']);
    }

}
