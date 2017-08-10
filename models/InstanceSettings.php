<?php

namespace app\models;
use app\queries\InstanceSettingsQuery;

/**
 * @property int $id
 * @property string $region_id
 * @property int $city_geo_id
 * @property bool $active
 * @property string $name,
 * @property int $city_prefix
 * @property int $country_id
 * @property int $city_id
 * @property bool $is_can_recalculate
 * @property bool $auto_lock_finance
 */
class InstanceSettings extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.instance_settings';
    }

    /**
     * @return InstanceSettingsQuery
     */
    public static function find()
    {
        return new InstanceSettingsQuery(get_called_class());
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'city_geo_id', 'city_prefix','city_id', 'country_id'], 'integer'],
            [['region_id', 'name'], 'string'],
            [['active','is_can_recalculate'], 'boolean'],
        ];
    }


}