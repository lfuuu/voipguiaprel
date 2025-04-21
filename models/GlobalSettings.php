<?php

namespace app\models;

use yii\db\ActiveRecord;

class GlobalSettings extends ActiveRecord
{
    public static function tableName()
    {
        return 'public.global_bill_settings';
    }

    public static function create(array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        return $item;
    }

    public function rules()
    {
        return [
            [['antifraud_error_check','antifraud_reject_check','antifraud_timeout_check'], 'boolean'],
        ];
    }
}
