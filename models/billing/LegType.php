<?php

namespace app\models\billing;

class LegType extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing.leg_type';
    }
}