<?php
namespace app\queries;

use app\models\Operator;
use app\models\OperatorRule;
use yii\db\ActiveQuery;

/**
 * @method OperatorRule[] all($db = null)
 * @property
 */
class OperatorRuleQuery extends ActiveQuery
{
    public function operator(Operator $operator)
    {
        return $this->andWhere(['operator_id' => $operator->id]);
    }
}