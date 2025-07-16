<?php

namespace app\models\auth;

use yii\db\ActiveRecord;

/**
 * Модель справочника типов коннекторов (таблица auth.connector_type)
 *
 * @property int    $id
 * @property string $type
 * @property string $description
 */
class SmsConnectorType extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'auth.connector_type';
    }

    public function rules(): array
    {
        return [
            [['type', 'description'], 'required'],
            ['type',        'string', 'max' => 50],
            ['description', 'string'],
            ['type',        'unique'],
        ];
    }
}
