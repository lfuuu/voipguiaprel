<?php

namespace app\models;
use app\queries\RouteTableRouteQuery;

/**
 * @property int $route_table_id
 * @property int $order
 * @property int $a_number_id
 * @property int $b_number_id
 * @property int $c_number_id
 * @property int $outcome_id
 * @property int $outcome_route_table_id
 * @property int $cpc_id
 * @property bool $is_locked
 * @property integer $header_rule_id
 * @property string $object_comment
 */
class RouteTableRoute extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.route_table_route';
    }

    public static function find()
    {
        return new RouteTableRouteQuery(get_called_class());
    }

    public static function create(RouteTable $routeTable, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->route_table_id = $routeTable->id;
        return $item;
    }

    public static function deleteByRouteTable(RouteTable $routeTable)
    {
        return self::deleteAll(['route_table_id' => $routeTable->id]);
    }

    public function rules()
    {
        return [
            [['a_number_id', 'b_number_id', 'c_number_id', 'outcome_id', 'outcome_route_table_id', 'cpc_id', 'header_rule_id'], 'integer'],
            [['is_locked'], 'boolean'],
            [['object_comment'], 'string', 'max' => \Yii::$app->params['commentMaxLength']],
        ];
    }

    public function extraFields()
    {
        return ['a_number', 'b_number', 'outcome', 'outcome_route_table', 'cpc_id'];
    }

    public function getANumber()
    {
        return $this->hasOne(Number::className(), ['id' => 'a_number_id']);
    }

    public function getBNumber()
    {
        return $this->hasOne(Number::className(), ['id' => 'b_number_id']);
    }

    public function getOutcome()
    {
        return $this->hasOne(Outcome::className(), ['id' => 'outcome_id']);
    }

    public function getOutcomeRouteTable()
    {
        return $this->hasOne(Outcome::className(), ['id' => 'outcome_route_table_id']);
    }
    
    public function getCpc()
    {
        return $this->hasOne(Cpc::className(), ['id' => 'cpc_id']);
    }
}