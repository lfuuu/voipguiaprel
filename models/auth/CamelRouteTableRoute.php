<?php

namespace app\models\auth;
use app\queries\auth\CamelRouteTableRouteQuery;

/**
 * @property int $camel_route_table_id
 * @property int $order
 * @property int $gt_id
 * @property int $a_number_id
 * @property string $b_number_regexp
 * @property int $outcome_id
 * @property string $outcome_args
 * @property bool $is_locked
 * @property string $object_comment
 */
class CamelRouteTableRoute extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.camel_route_table_route';
    }

    public static function find()
    {
        return new CamelRouteTableRouteQuery(get_called_class());
    }

    public static function create(CamelRouteTable $routeTable, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->camel_route_table_id = $routeTable->id;
        return $item;
    }

    public static function deleteByRouteTable(CamelRouteTable $routeTable)
    {
        return self::deleteAll(['camel_route_table_id' => $routeTable->id]);
    }

    public function rules()
    {
        return [
            [['b_number_regexp', 'object_comment', 'outcome_args', 'object_comment'], 'string'],
            [['order', 'gt_id', 'a_number_id', 'outcome_id'], 'integer'],
            [['is_locked'], 'boolean'],
        ];
    }
}