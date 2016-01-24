<?php

namespace app\models;

/**
 * @property int $id
 * @property int $instance_id
 * @property string $name
 * @property
 */
class NetworkConfig extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'voip.network_config';
    }
}