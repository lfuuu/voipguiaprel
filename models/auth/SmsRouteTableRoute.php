<?php

namespace app\models\auth;
use app\queries\auth\SmsRouteTableRouteQuery;

/**
 * @property int $a2psms_route_table_id
 * @property int $order
 * @property int $b_number_id
 * @property int $a_number_id
 * @property int $nnp_pricelist_id
 * @property int $a2psms_outcome_id
 * @property bool $is_locked
 * @property string $object_comment
 * @property int $a2psms_outcome_route_table_id
 */
class SmsRouteTableRoute extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.a2psms_route_table_route';
    }

    public static function find()
    {
        return new SmsRouteTableRouteQuery(get_called_class());
    }

    public static function create(SmsRouteTable $routeTable, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->a2psms_route_table_id = $routeTable->id;
        return $item;
    }

    public static function deleteByRouteTable(SmsRouteTable $routeTable)
    {
        return self::deleteAll(['a2psms_route_table_id' => $routeTable->id]);
    }

    public function rules()
    {
        return [
            [['object_comment'], 'string'],
            [['order', 'a_number_id', 'b_number_id', 'a2psms_outcome_id', 'a2psms_outcome_route_table_id', 'nnp_pricelist_id'], 'integer'],
            [['is_locked'], 'boolean'],
        ];
    }
}