<?php

namespace app\controllers\json\api_billing;

use app\classes\JsonController;

class ApiPricelistController extends JsonController
{
    protected $modelName = 'app\models\billing_api\ApiPricelist';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $createPermission = 'api_billing_api_pricelist_create';
    protected $listPermission = 'api_billing_api_pricelist_list';
    protected $editPermission = 'api_billing_api_pricelist_edit';
    protected $deletePermission = 'api_billing_api_pricelist_delete';
}
