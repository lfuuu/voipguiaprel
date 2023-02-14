<?php

namespace app\models\nnp;

/**
 * @property int $id
 * @property string $name
 * @property int $country_prefix
 * @property int $country_code
 */
class OperatorMncToNnp extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.operator_mnc_to_nnp';
    }

}