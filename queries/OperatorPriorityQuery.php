<?php
namespace app\queries;

use app\models\Operator;
use app\models\OperatorPriority;
use yii\db\ActiveQuery;

/**
 * @method OperatorPriority[] all($db = null)
 * @property
 */
class OperatorPriorityQuery extends ActiveQuery
{
    public function operator(Operator $operator)
    {
        return $this->andWhere(['operator_id' => $operator->id]);
    }
}