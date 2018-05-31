<?php

namespace app\models\voip;

use app\queries\voip\PricelistQuery;

/**
 * Class Pricelist
 * @package app\models\voip
 */
class Pricelist extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'voip.pricelist';
    }

    public static function find()
    {
        return new PricelistQuery(get_called_class());
    }

    public function rules()
    {
        return [
            [['is_global'], 'boolean']
        ];
    }
}