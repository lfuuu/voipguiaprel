<?php
namespace app\models\sms_raw;

use yii\db\ActiveRecord;

class SmsRaw extends ActiveRecord
{
    public static function tableName()
    {
        return 'sms_raw.sms_raw';
    }
}
