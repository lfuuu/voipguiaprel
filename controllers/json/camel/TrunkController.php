<?php

namespace app\controllers\json\camel;

use app\classes\JsonController;

class TrunkController extends JsonController
{
    protected $modelName = 'app\models\auth\CamelTrunk';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $createPermission = 'camel_trunk_create';
    protected $listPermission = 'camel_trunk_list';
    protected $editPermission = 'camel_trunk_edit';
    protected $deletePermission = 'camel_trunk_delete';
}
