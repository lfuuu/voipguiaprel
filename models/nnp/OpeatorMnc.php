<?php

namespace app\models\nnp;

/**
 * @property int $id
 * @property string $name
 */
class OperatorMnc extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.operator_mnc';
    }

}