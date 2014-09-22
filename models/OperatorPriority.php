<?php
namespace app\models;
use app\queries\OperatorPriorityQuery;

/**
 * @property int $id
 * @property int $operator_id
 * @property int $order
 * @property int $priority
 * @property int $prefixlist_id
 * @property
 */
class OperatorPriority extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.operator_priority';
    }

    public static function find()
    {
        return new OperatorPriorityQuery(get_called_class());
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
            [['priority'], 'integer', 'min'=> -10, 'max' => 10],
            [['prefixlist_id'], 'integer'],
        ];
    }
}