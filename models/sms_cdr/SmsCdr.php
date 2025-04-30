<?php
namespace app\models\sms_cdr;

use yii\db\ActiveRecord;

class SmsCdr extends ActiveRecord
{
    public static function tableName() { return 'sms_cdr.sms_cdr'; }

    public function rules()
    {
        return [
            [['id','server_id','type','direction','quantity','timestamp'], 'integer'],
            [['dt_create'], 'safe'],
            [['msisdn','imsi','destination','mcc','mnc','sessionid'], 'string'],
        ];
    }
}
