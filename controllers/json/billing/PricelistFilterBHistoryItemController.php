<?php

namespace app\controllers\json\billing;

use app\classes\JsonController;

class PricelistFilterBHistoryItemController extends JsonController
{
    protected $modelName = 'app\models\billing_uu\PricelistFilterBHistoryItem';
    protected $idParamName = 'id';
    protected $nameParamName = 'description';
    protected $listPermission = 'pricelist_filter_b_history_list';
    protected $readWhere = ['pricelist_filter_b_history_id'];
    
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
