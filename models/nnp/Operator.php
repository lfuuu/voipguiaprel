<?php

namespace app\models\nnp;

/**
 * @property int $id
 * @property string $name
 * @property int $country_prefix
 * @property int $cnt
 * @property int $country_code
 */
class Operator extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.operator';
    }

}