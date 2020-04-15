<?php

namespace app\controllers\json\billing;

use app\classes\JsonController;

class PricelistPrefixPriceHistoryItemController extends JsonController
{
    protected $modelName = 'app\models\billing_uu\PricelistPrefixPriceHistoryItem';
    protected $idParamName = 'id';
    protected $nameParamName = 'prefix_b';
    protected $listPermission = 'pricelist_prefix_price_history_list';
    protected $readWhere = ['pricelist_prefix_price_history_id'];
    
    public function actionGet()
    {
        // do_nothing
    }
    
    public function actionList()
    {
        // do_nothing
    }
    
    public function actionSave()
    {
        // do_nothing
    }
    
    public function actionDelete()
    {
        // do_nothing
    }
}
