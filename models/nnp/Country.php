<?php

namespace app\models\nnp;

/**
 * @property int $code
 * @property string $name
 * @property string $name_rus
 * @property int $prefix
 * @property int[] $prefixes -- Начальные цифры телефонных номеров (префикс страны и начало NDC)
 */
class Country extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.country';
    }

}