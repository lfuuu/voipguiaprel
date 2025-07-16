<?php
// models/auth/SmsConnectorProto.php

namespace app\models\auth;

use yii\db\ActiveRecord;

/**
 * @property int    $id
 * @property string $type
 * @property string $description
 */
class SmsConnectorProto extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'auth.connector_proto';
    }

    public function rules(): array
    {
        return [
            [['type','description'], 'required'],
            ['type',        'string', 'max' => 50],
            ['description', 'string'],
            ['type',        'unique'],
        ];
    }
}
