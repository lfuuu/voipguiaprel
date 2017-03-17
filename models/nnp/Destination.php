<?php

namespace app\models\nnp;

/**
 * @property int $id
 * @property string $name
 */
class Destination extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.destination';
    }

}