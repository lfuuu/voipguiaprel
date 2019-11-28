<?php

namespace app\controllers\json\api_billing;

use app\classes\JsonController;

class ApiController extends JsonController
{
    protected $modelName = 'app\models\billing_api\Api';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $readWhere = ['server_id'];
    protected $createPermission = 'api_billing_api_create';
    protected $listPermission = 'api_billing_api_list';
    protected $editPermission = 'api_billing_api_edit';
    protected $deletePermission = 'api_billing_api_delete';
}
