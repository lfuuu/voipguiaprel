<?php

namespace app\models\billing;
use app\queries\billing\DisconnectCauseQuery;

/**
 *
 */
class DisconnectCause extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing.disconnect_cause';
    }

    public static function find()
    {
        return new DisconnectCauseQuery(get_called_class());
    }
}