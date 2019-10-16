<?php

namespace app\controllers\json\camel;

use app\classes\JsonController;

class GtController extends JsonController
{
    protected $modelName = 'app\models\auth\CamelGt';
    protected $idParamName = 'id';
    protected $nameParamName = 'gt';
    protected $createPermission = 'camel_gt_create';
    protected $listPermission = 'camel_gt_list';
    protected $editPermission = 'camel_gt_edit';
    protected $deletePermission = 'camel_gt_delete';
}
