<?php
namespace app\models;

use app\queries\TrunkPriorityQuery;

class TrunkOrigTerm extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'billing.trunk_orig_term';
    }

    public static function find()
    {
        return new TrunkPriorityQuery(get_called_class());
    }

}