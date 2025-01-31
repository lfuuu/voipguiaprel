<?php

namespace app\models\sorm;


/**
 * Модель для таблицы 'adapter'
 *
 * @property int $id
 * @property int $server_id
 * @property bool $sw_shared
 * @property string $name
 * @property string $address
 * @property bool $add_out_trunk
 * @property bool $del_in_trunk
 * @property string $created_at
 * @property string $updated_at
 */
class TelemetryReceiver extends \yii\db\ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'auth.telemetry_receiver';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getServer()
    {
        return $this->hasOne(Server::className(), ['id' => 'server_id']);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['server_id', 'name', 'address'], 'required'],
            [['server_id'], 'integer'],
            [['sw_shared', 'add_out_trunk', 'del_in_trunk'], 'boolean'],
            [['name'], 'string', 'max' => 255],
            [['address'], 'safe'],
        ];
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'server_id' => 'Server ID',
            'sw_shared' => 'Доступен на хабе',
            'name' => 'Название адаптера',
            'address' => 'Адрес адаптера',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ];
    }

    /**
     *
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
{
    if (parent::beforeSave($insert)) {
        $this->address = json_encode($this->address);
        return true;
    }
    return false;
}

public function afterFind()
{
    parent::afterFind();
    $this->address = json_decode($this->address, true);
}

}
