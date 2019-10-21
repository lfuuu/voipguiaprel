<?php

namespace app\controllers\json\camel;

use app\classes\JsonController;

class TestGroupController extends JsonController
{
    protected $modelName = 'app\models\auth\CamelTestGroup';
    protected $idParamName = 'id';
    protected $nameParamName = 'group_name';
    protected $createPermission = 'camel_test_group_create';
    protected $listPermission = 'camel_test_group_list';
    protected $editPermission = 'camel_test_group_edit';
    protected $deletePermission = 'camel_test_group_delete';
}
