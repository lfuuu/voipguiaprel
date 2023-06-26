<?php

namespace app\controllers\json\camel;

use app\classes\JsonController;
use app\models\auth\CamelRouteTableRoute;

class RouteTableController extends JsonController
{
    protected $modelName = 'app\models\auth\CamelRouteTable';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $withDependencies = ['routes'];
    protected $readWhere = ['server_id'];
    protected $createPermission = 'camel_route_table_create';
    protected $listPermission = 'camel_route_table_list';
    protected $editPermission = 'camel_route_table_edit';
    protected $deletePermission = 'camel_route_table_delete';

    protected function performAfterSaveActions($item, $request)
    {
        CamelRouteTableRoute::deleteByRouteTable($item);
        $order = 1;
        foreach ($this->request['routes'] as $routeData) {
            $route = CamelRouteTableRoute::create($item, $routeData);
            $route->order = $order;
            if (!$route->save()) {
                throw new FormValidationException($route);
            }
            $order++;
        }
    }
    
    public function actionGet()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;
        $item = $modelName::find()
            ->select($this->getSelect)
            ->with($this->withDependencies)
            ->where([$this->idParamName => $this->request[$this->idParamName]])
            ->asArray()
            ->one();

        if ($item === null) {
            throw new HttpException(404, $modelName . ' не найден');
        }

        $item = $this->performAfterGetActions($item);

        return $item;
    }
}
