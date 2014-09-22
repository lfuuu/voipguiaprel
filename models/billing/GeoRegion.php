<?php

namespace app\models\billing;

use \Yii;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;


class GeoRegion extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'geo.region';
    }
}