<?php

namespace app\models\geo;

/**
 * @property
 */
class City extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'geo.city';
    }

}