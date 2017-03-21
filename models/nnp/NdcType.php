<?php

namespace app\models\nnp;

/**
 * @property int $id
 * @property string $name
 */
class NdcType extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.ndc_type';
    }

}
