<?php

namespace app\models\auth;

use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property string $name
 * @property string $description
 * @property string $ip
 * @property string $host
 * @property string $type
 */
class SmsGate extends ActiveRecord
{
    public static function tableName()
    {
        return 'auth.sms_gate';
    }

    /**
     * Создает новый экземпляр модели с загруженными данными
     *
     * @param array|null $data
     * @return static
     */
    public static function create(array $data = null)
    {
        $item = new static();
        $item->load($data, '');
        return $item;
    }

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'description', 'ip', 'host', 'type'], 'string'],
            [['name'], 'required'],
            ['type', 'in', 'range' => ['SMSGATE', 'Yate']],
        ];
    }
}
