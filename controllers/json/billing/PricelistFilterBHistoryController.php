<?php

namespace app\controllers\json\billing;

use app\classes\JsonController;
use app\controllers\ResetterController;
use app\exceptions\FormValidationException;
use app\models\billing_uu\PricelistFilterB;
use app\models\billing_uu\PricelistFilterA;
use app\models\billing_uu\PricelistPrefixPrice;
use app\models\billing_uu\PricelistFilterBHistory;
use app\models\billing_uu\PricelistFilterBHistoryItem;
use app\models\billing_uu\PricelistPrefixPriceHistory;
use app\models\billing_uu\PricelistPrefixPriceHistoryItem;
use yii\web\ForbiddenHttpException;

class PricelistFilterBHistoryController extends JsonController
{
    protected $modelName = 'app\models\billing_uu\PricelistFitlerBHistory';
    protected $idParamName = 'id';
    protected $nameParamName = 'date_from';
    protected $editPermission = 'pricelist_filter_b_history_list';
    protected $withDependencies = ['prefixPriceList'];

    const CHUNK_SIZE_INSERT = 20000;
    const CHUNK_SIZE_DELETE = 5000;
    const CHUNK_SIZE_UPDATE = 5000;

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

    public function actionUndo()
    {
        if (!\Yii::$app->user->can($this->editPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $id = $this->request['id'];
        $itemP = PricelistFilterBHistory::findOne($id);

        $transaction = PricelistFilterB::getDb()->beginTransaction();
        $transaction2 = PricelistPrefixPriceHistory::getDb()->beginTransaction();
        try {

            $historyItems = PricelistFilterBHistoryItem::findAll(['pricelist_filter_b_history_id' => $itemP->id]);
            foreach ($historyItems as $historyItem) {
                $filterBToDelete = PricelistFilterB::find()
                    ->where(['description' => $historyItem['description']])
                    ->andWhere(['nnp_filter' => $historyItem['nnp_filter_id']])
                    ->andWhere(['pricelist_filter_a_id' => $itemP->pricelist_filter_a_id])
                    ->one();

                if ($historyItem->type != 'add') {
                    $currentPrefixPriceHistory = PricelistPrefixPriceHistory::find()->where(['pricelist_filter_b_id' => $filterBToDelete['id']])->orderBy(['id' => SORT_DESC])->one();

                    $dataBefore = json_decode($currentPrefixPriceHistory['data_before'], true);
                    $count = count($dataBefore);

                    $currentData = PricelistPrefixPrice::find()
                        ->where(['pricelist_filter_b_id' => $currentPrefixPriceHistory['pricelist_filter_b_id']])
                        ->indexBy('id')
                        ->asArray()
                        ->all();

                    $dataToUpdateHistory = [];
                    $dataToDelete = [];
                    $dataToInsert = [];
                    $beforeIds = [];
                    for ($i = 0; $i < $count; $i++) {
                        $id = $dataBefore[$i]['id'];
                        $itemBefore = $dataBefore[$i];
                        $beforeIds[] = $id;

                        if (!isset($currentData[$id])) {
                            $dataToInsert[] = [
                                $itemBefore['id'], $itemBefore['pricelist_filter_b_id'], $itemBefore['prefix_b'],
                                $itemBefore['b_number_price'], $itemBefore['change_flag'], $itemBefore['date_from'],
                                $itemBefore['date_to'], $itemBefore['b_number_connect_price'], $itemBefore['history_id']
                            ];
                        } else {
                            $itemCurrent = $currentData[$id];
                            if (
                                $itemCurrent['prefix_b'] != $itemBefore['prefix_b'] ||
                                $itemCurrent['b_number_price'] != $itemBefore['b_number_price'] ||
                                $itemCurrent['change_flag'] != $itemBefore['change_flag'] ||
                                $itemCurrent['date_from'] != $itemBefore['date_from'] ||
                                $itemCurrent['date_to'] != $itemBefore['date_to'] ||
                                $itemCurrent['b_number_connect_price'] != $itemBefore['b_number_connect_price']
                            ) {
                                $dataToDelete[] = $id;

                                $dataToInsert[] = [
                                    $itemBefore['id'], $itemBefore['pricelist_filter_b_id'], $itemBefore['prefix_b'],
                                    $itemBefore['b_number_price'], $itemBefore['change_flag'], $itemBefore['date_from'],
                                    $itemBefore['date_to'], $itemBefore['b_number_connect_price'], $itemBefore['history_id']
                                ];
                            } else if ($itemCurrent['history_id'] != $itemBefore['history_id']) {
                                $dataToUpdateHistory[$itemCurrent['history_id']][] = $id;
                            }

                            unset($currentData[$id]);
                        }

                        unset($dataBefore[$i]);
                    }

                    $dataToDelete = array_merge($dataToDelete, array_diff(array_keys($currentData), $beforeIds));
                    foreach (array_chunk($dataToDelete, self::CHUNK_SIZE_INSERT) as $chunk) {
                        PricelistPrefixPrice::deleteAll(['id' => $chunk]);
                    }
                    unset($dataToDelete);

                    foreach ($dataToUpdateHistory  as $historyId => $ids) {
                        foreach (array_chunk($ids, self::CHUNK_SIZE_DELETE) as $chunk) {
                            \Yii::$app->db->createCommand()->update(PricelistPrefixPrice::tableName(), [
                                'history_id' => $historyId,
                            ], ['id' => $chunk])
                                ->execute();
                        }
                    }
                    unset($dataToUpdateHistory);

                    foreach (array_chunk($dataToInsert, self::CHUNK_SIZE_UPDATE, true) as $chunk) {
                        \Yii::$app->db->createCommand()->batchInsert(
                            PricelistPrefixPrice::tableName(),
                            [
                                'id', 'pricelist_filter_b_id', 'prefix_b', 'b_number_price', 'change_flag',
                                'date_from', 'date_to', 'b_number_connect_price', 'history_id'
                            ],
                            $chunk
                        )->execute();
                    }
                    unset($dataToInsert);
                    $currentPrefixPriceHistory->delete();
                } else {
                    if (!$filterBToDelete->delete()) {
                        throw new FormValidationException($itemP);
                    }
                }
            }
            $itemP->delete();
            $transaction2->commit();
            $transaction->commit();
        } finally {
            if ($transaction2->getIsActive())
                $transaction2->rollBack();
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }
}
