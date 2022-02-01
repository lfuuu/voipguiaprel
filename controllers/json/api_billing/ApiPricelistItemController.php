<?php

namespace app\controllers\json\api_billing;

use app\classes\JsonController;

class ApiPricelistItemController extends JsonController
{
    protected $modelName = 'app\models\billing_api\ApiPricelistItem';
    protected $idParamName = 'id';
    protected $nameParamName = 'id';
    protected $createPermission = 'api_billing_api_pricelist_item_create';
    protected $listPermission = 'api_billing_api_pricelist_item_list';
    protected $editPermission = 'api_billing_api_pricelist_item_edit';
    protected $deletePermission = 'api_billing_api_pricelist_item_delete';

    public function actionRead()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $items =
            $modelName::find()
                ->alias('api')
                ->select(['api.*', 'api_name' => 'a.name', 'api_method_name' => 'am.name', 'pricelist_name' => 'ap.name', 'is_active' => 'ap.is_active'])
                ->innerJoin('billing_api.api a', 'a.id = api.api_id')
                ->innerJoin('billing_api.api_method am', 'am.id = api.api_method_id')
                ->innerJoin('billing_api.api_pricelist ap', 'ap.id = api.pricelist_id')
                ->orderBy('api.' . $this->nameParamName)
                ->where(['a.server_id' => $this->request['server_id']])
                ->asArray()
                ->all();

        $items = $this->performAfterReadActions($items);

        return $items;
    }
}
