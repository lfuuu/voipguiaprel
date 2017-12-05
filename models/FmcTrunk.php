<?php

namespace app\models;

class FmcTrunk extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing.fmc_trunks';
    }

    public function rules()
    {
        return [
            [['fmc_trunk_id'], 'required'],
            [['name'], 'required'],
        ];
    }
}