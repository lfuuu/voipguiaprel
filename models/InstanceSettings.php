<?php

namespace app\models;
use app\queries\InstanceSettingsQuery;

class InstanceSettings extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing.instance_settings';
    }

    public static function find()
    {
        return new InstanceSettingsQuery(get_called_class());
    }

    public function rules()
    {
        return [
            [['id','region_id', 'city_geo_id', 'city_prefix','city_id'], 'integer'],
            [['active','is_can_recalculate'], 'boolean'],
        ];
    }


}