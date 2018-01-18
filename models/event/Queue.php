<?php

namespace app\models\event;
use app\queries\QueueQuery;

/**
 * @property int $server_id
 * @property string $event
 * @property int $param
 * @property int $version
 * @property
 */
class Queue extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'event.queue';
    }

    public static function find()
    {
        return new QueueQuery(get_called_class());
    }
}