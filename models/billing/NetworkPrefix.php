<?php
namespace app\models\billing;

class NetworkPrefix extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing.network_prefix';
    }
}