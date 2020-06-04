<?php

namespace app\models\materialized_view;
use app\queries\materialized_view\NdcQuery;

/**
 * @property int $id
 * @property string $name
 * @property string $country_code
 * @property
 */
class Ndc extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'nnp.ndc_materialized_view';
    }

    public static function find()
    {
        return new NdcQuery(get_called_class());
    }
}