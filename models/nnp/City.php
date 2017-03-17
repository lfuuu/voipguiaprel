<?php

namespace app\models\nnp;

/**
 * @property int $id
 * @property string $name
 * @property int $country_prefix
 * @property int $country_code
 * @property int $region_id
 */
class City extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.city';
    }

}