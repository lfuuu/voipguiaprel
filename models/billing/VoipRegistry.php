<?php

namespace app\models\billing;

use \Yii;

/**
 * @property
 */
class VoipRegistry extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing.voip_registry';
    }
}