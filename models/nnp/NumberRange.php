<?php

namespace app\models\nnp;

/**
 * @property int $id
 * @property int $country_code
 * @property int $ndc
 * @property int $number_from
 * @property int $number_to
 * @property bool $is_mob
 * @property bool $is_active
 * @property string $operator_source
 * @property int $operator_id
 * @property string $region_source
 * @property int $region_id
 * @property string $insert_time
 * @property int $insert_user_id
 * @property string $update_time
 * @property int $update_user_id
 * @property int $city_id
 * @property int $country_prefix
 * @property int $full_number_from
 * @property int $full_number_to
 * @property int $ndc_type_id
 * @property string $date_stop
 * @property string $date_resolution
 * @property string $detail_resolution
 * @property string $status_number
 * @property string $ndc_type_source
 * @property string $city_source
 */
class NumberRange extends \yii\db\ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName()
    {
        return 'nnp.number_range';
    }

}