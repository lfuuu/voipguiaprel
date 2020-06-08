<?php

namespace app\controllers\json\billing;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\billing_uu\PricelistPrefixPrice;
use app\models\billing_uu\PricelistPrefixPriceHistory;
use yii\web\ForbiddenHttpException;

class PricelistFilterBHistoryController extends JsonController
{
    protected $modelName = 'app\models\billing_uu\PricelistFitlerBHistory';
    protected $idParamName = 'id';
    protected $nameParamName = 'date_from';
    protected $editPermission = 'pricelist_filter_b_history_list';
    protected $withDependencies = ['prefixPriceList'];
    
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
    
    public function actionUndoImport()
    {
        if (!\Yii::$app->user->can($this->editPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        // $id = $this->request['id'];
        
        // $item = PricelistPrefixPriceHistory::findOne($id);
        
        // $transaction = PricelistPrefixPriceHistory::getDb()->beginTransaction();
        // try {
        //     PricelistPrefixPrice::deleteAll('pricelist_filter_b_id = :b_id', [':b_id' => $item->pricelist_filter_b_id]);
        
        //     $dataBefore = json_decode($item->data_before, true);
            
        //     foreach ($dataBefore as $prefixToSave) {
        //         $prefixCreatedItem = PricelistPrefixPrice::create($prefixToSave);
        //         if (!$prefixCreatedItem->save()) {
        //             throw new FormValidationException($item);
        //         }
        //     }
            
        //     $item->delete();
            
        //     $transaction->commit();
        // } finally {
        //     if ($transaction->getIsActive())
        //         $transaction->rollBack();
        // }
    }
}
