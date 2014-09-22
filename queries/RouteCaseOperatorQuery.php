<?php
namespace app\queries;

use app\models\RouteCase;
use app\models\RouteCaseOperator;
use yii\db\ActiveQuery;

/**
 * @method RouteCaseOperator[] all($db = null)
 * @property
 */
class RouteCaseOperatorQuery extends ActiveQuery
{
    public function routeCase(RouteCase $routeCase)
    {
        return $this->andWhere(['route_case_id' => $routeCase->id]);
    }
}