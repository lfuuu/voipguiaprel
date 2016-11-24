<?php

namespace app\models\billing;

use \Yii;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;


class GeoCountry extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'geo.country';
    }
}