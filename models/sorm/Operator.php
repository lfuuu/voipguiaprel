<?php

namespace app\models\sorm;
use app\queries\sorm\OperatorQuery;

/**
 * @property int $id
 * @property
 */
class Operator extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'copm.operator';
    }

    public static function find()
    {
        return new OperatorQuery(get_called_class());
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCommutator()
    {
        return $this->hasOne(Commutator::className(), ['operator_id' => 'id']);
    }
}