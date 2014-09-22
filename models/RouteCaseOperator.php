<?php
namespace app\models;

use app\queries\RouteCaseOperatorQuery;

/**
 * @property int $route_case_id
 * @property int $operator_id
 * @property int $priority
 * @property int $weight
 * @property
 */
class RouteCaseOperator extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'auth.route_case_operator';
    }

    public static function find()
    {
        return new RouteCaseOperatorQuery(get_called_class());
    }

    public static function create(RouteCase $routeCase, array $data = null)
    {
        $item = new self();
        $item->load($data, '');
        $item->route_case_id = $routeCase->id;
        return $item;
    }

    public static function deleteByRouteCase(RouteCase $routeCase)
    {
        return self::deleteAll(['route_case_id' => $routeCase->id]);
    }


    public function rules()
    {
        return [
            [['priority'], 'integer', 'min'=> 1, 'max' => 10],
            [['weight'], 'integer', 'min'=> 1, 'max' => 100],
            [['operator_id'], 'integer'],
        ];
    }

    public function extraFields()
    {
        return ['operator'];
    }

    public function getOperator()
    {
        return $this->hasOne(Operator::className(), ['id' => 'operator_id']);
    }

}