<?php

namespace app\models\billing;

use \Yii;

/**
 * @property
 */
class VoipNumber extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing.voip_number';
    }
}