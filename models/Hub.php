<?php

namespace app\models;

use Yii;

/**
 * @property integer $id
 * @property string $dt
 * @property string $name
 * @property string $note
 *
 * @property Server $servers
 * @property InstanceSettings $instanceSettings
 */
class Hub extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.hub';
    }

    /**
     * @return array
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
     * @return array
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServers()
    {
        return $this->hasMany(Server::className(), ['hub_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInstanceSettings()
    {
        return $this->hasOne(InstanceSettings::className(), ['id' => 'id']);
    }



}
