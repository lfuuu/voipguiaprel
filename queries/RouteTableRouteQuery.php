<?php
namespace app\queries;

use app\models\RouteTable;
use app\models\RouteTableRoute;
use yii\db\ActiveQuery;

/**
 * @method RouteTableRoute[] all($db = null)
 * @property
 */
class RouteTableRouteQuery extends ActiveQuery
{
    public function routeTable(RouteTable $routeTable)
    {
        return $this->andWhere(['route_table_id' => $routeTable->id]);
    }
}