<?php

namespace app\controllers\json\camel;

use app\classes\JsonController;

class RouteTableController extends JsonController
{
    protected $modelName = 'app\models\auth\CamelRouteTable';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $createPermission = 'camel_route_table_create';
    protected $listPermission = 'camel_route_table_list';
    protected $editPermission = 'camel_route_table_edit';
    protected $deletePermission = 'camel_route_table_delete';
}
