<?php

namespace app\models\billing;
use app\queries\billing\ClientQuery;

/**
 *
 */
class Client extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing.clients';
    }

    public static function find()
    {
        return new ClientQuery(get_called_class());
    }
}