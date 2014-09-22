<?php

namespace app\models\billing;

use \Yii;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;


class BillingDefs extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing.defs';
    }
}