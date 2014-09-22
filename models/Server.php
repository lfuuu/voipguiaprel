<?php

namespace app\models;
use app\queries\ServerQuery;

/**
 * @property int $id
 * @property string $name
 * @property
 */
class Server extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'public.server';
    }

    public static function find()
    {
        return new ServerQuery(get_called_class());
    }

    public function getActualConfig()
    {
        return
            ConfigVersion::find()
                ->where(['server_id' => $this->id, 'status_id' => ConfigVersion::STATUS_ACTIVE])
                ->one();

    }
}