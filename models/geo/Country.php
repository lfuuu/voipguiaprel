<?php

namespace app\models\geo;

/**
 * @property
 */
class Country extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'geo.country';
    }

}