<?php

namespace app\controllers;

use app\classes\BaseController;
use app\models\billing_uu\PricelistPrefixPrice;
use app\models\billing_uu\PricelistPrefixPriceHistory;
use yii\web\ForbiddenHttpException;

class ResetterController extends BaseController
{
    protected $modelName = 'app\models\billing_uu\PricelistPrefixPriceHistory';
    protected $idParamName = 'id';
    protected $nameParamName = 'date_from';
    protected $editPermission = 'pricelist_prefix_price_history_list';
    protected $withDependencies = ['prefixPriceList'];
    
    public function actionReset($id)
    {
        if (!\Yii::$app->user->can($this->editPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        
        $startTime = microtime(true);
        
        $item = PricelistPrefixPriceHistory::findOne($id);
        
        $transaction = PricelistPrefixPriceHistory::getDb()->beginTransaction();
        try {
            $dataBefore = json_decode($item->data_before, true);
            $count = count($dataBefore);
            
            $dataToInsert = [];
            
            for ($i = 0; $i < $count; $i++) {
                $dataToInsert[] = [
                    $dataBefore[$i]['id'], $dataBefore[$i]['pricelist_filter_b_id'], $dataBefore[$i]['prefix_b'],
                    $dataBefore[$i]['b_number_price'], $dataBefore[$i]['change_flag'], $dataBefore[$i]['date_from'],
                    $dataBefore[$i]['date_to'], $dataBefore[$i]['b_number_connect_price'], $dataBefore[$i]['history_id']
                ];
                
                unset($dataBefore[$i]);
            }
            
            PricelistPrefixPrice::deleteAll('pricelist_filter_b_id = :b_id', [':b_id' => $item->pricelist_filter_b_id]);
            
            \Yii::$app->db->createCommand()->batchInsert(
                'billing_uu.pricelist_prefix_price',
                [
                    'id', 'pricelist_filter_b_id', 'prefix_b', 'b_number_price', 'change_flag',
                    'date_from', 'date_to', 'b_number_connect_price', 'history_id'
                ],
                $dataToInsert
            )->execute();
            
            $item->delete();
            
            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
        
        $endTime = microtime(true);
        
        return $this->render('reset', [
            'delta_time' => round($endTime - $startTime, 2)
        ]); 
    }
}
