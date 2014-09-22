<?php
namespace app\models;
use app\queries\OperatorRuleQuery;

/**
 * @property int $id
 * @property int $operator_id
 * @property bool $outgoing
 * @property int $order
 * @property int $trunk_group_id
 * @property int $prefixlist_id
 * @property
 */
class OperatorRule extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.operator_rule';
    }

    public static function find()
    {
        return new OperatorRuleQuery(get_called_class());
    }

    public static function create(Operator $operator, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->operator_id = $operator->id;
        return $item;
    }

    public static function deleteByOperator(Operator $operator)
    {
        return self::deleteAll(['operator_id' => $operator->id]);
    }

    public function rules()
    {
        return [
            [['outgoing'], 'boolean'],
            [['prefixlist_id', 'trunk_group_id'], 'integer'],
        ];
    }
}