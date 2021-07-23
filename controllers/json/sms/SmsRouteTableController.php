<?php

namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\auth\SmsRouteTableRoute;

class SmsRouteTableController extends JsonController
{
    protected $modelName = 'app\models\auth\SmsRouteTable';
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
        SmsRouteTableRoute::deleteByRouteTable($item);
        $order = 1;
        foreach ($this->request['routes'] as $routeData) {
            $route = SmsRouteTableRoute::create($item, $routeData);
            $route->order = $order;
            if (!$route->save()) {
                throw new FormValidationException($route);
            }
            $order++;
        }
    }
}