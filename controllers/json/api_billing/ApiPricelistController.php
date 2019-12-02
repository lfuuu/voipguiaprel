<?php

namespace app\controllers\json\api_billing;

use app\classes\JsonController;
use app\models\billing_api\ApiPricelistItem;

class ApiPricelistController extends JsonController
{
    protected $modelName = 'app\models\billing_api\ApiPricelist';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $withDependencies = ['items'];
    protected $createPermission = 'api_billing_api_pricelist_create';
    protected $listPermission = 'api_billing_api_pricelist_list';
    protected $editPermission = 'api_billing_api_pricelist_edit';
    protected $deletePermission = 'api_billing_api_pricelist_delete';

    protected function performBeforeSaveActions($item, $request)
    {
        ApiPricelistItem::deleteByPricelist($item);
        if (isset($this->request['items'])) {
            foreach ($this->request['items'] as $itemData) {
                $subitem = ApiPricelistItem::create($itemData, $item);
                if (!$subitem->save()) {
                    throw new FormValidationException($subitem);
                }
            }
        }
    }
}
