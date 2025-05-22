<?php
namespace app\models\a2p_sms_cdr;

use yii\db\ActiveRecord;

class A2pSmsCdr extends ActiveRecord
{
    public static function tableName()
    {
        return 'a2p_sms_cdr.a2p_sms_cdr';
    }

    public function rules()
    {
        return [
            [['id','server_id','sms_id','concatenation'], 'integer'],
            [['dt_create'], 'safe'],
            [['src_number','dst_number','src_route','dst_route','status','props'], 'string'],
        ];
    }
}
