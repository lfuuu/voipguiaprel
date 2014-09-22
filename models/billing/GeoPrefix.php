<?php

namespace app\models\billing;

use \Yii;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;


class GeoPrefix extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'geo.prefix';
    }

    public function extraFields()
    {
        return ['geo'];
    }

    public function getGeo()
    {
        return $this->hasOne(Geo::className(), ['id' => 'geo_id']);
    }
}