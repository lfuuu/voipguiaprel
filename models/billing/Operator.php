<?php

namespace app\models\billing;

use \Yii;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;

/**
 * @property int $region
 * @property int $id
 * @property int $default_pricelist_id
 * @property
 */
class Operator extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'voip.operator';
    }
}