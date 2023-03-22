<?php

namespace app\models;

use app\queries\AttributeQuery;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 */
class Currency extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'public.currency';
    }
}