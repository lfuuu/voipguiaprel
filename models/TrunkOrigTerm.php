<?php
namespace app\models;

use app\queries\TrunkPriorityQuery;

class TrunkOrigTerm extends \yii\db\ActiveRecord
{

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'billing.trunk_orig_term';
    }

    /**
     * @return TrunkPriorityQuery
     */
    public static function find()
    {
        return new TrunkPriorityQuery(get_called_class());
    }

}