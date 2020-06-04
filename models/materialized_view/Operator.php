<?php

namespace app\models\materialized_view;
use app\queries\materialized_view\OperatorQuery;

/**
 * @property int $id
 * @property string $name
 * @property string $country_code
 * @property
 */
class Operator extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'nnp.operator_materialized_view';
    }

    public static function find()
    {
        return new OperatorQuery(get_called_class());
    }
}