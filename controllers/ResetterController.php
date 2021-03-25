<?php

namespace app\controllers;

use app\classes\BaseController;
use app\models\billing_uu\PricelistPrefixPrice;
use app\models\billing_uu\PricelistPrefixPriceHistory;
use Exception;
use yii\web\ForbiddenHttpException;

class ResetterController extends BaseController
{
    const CHUNK_SIZE_INSERT = 20000;
    const CHUNK_SIZE_DELETE = 5000;
    const CHUNK_SIZE_UPDATE = 5000;

    protected $modelName = PricelistPrefixPriceHistory::class;
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
        if (!isset($item)) {
            throw new Exception('запись не существует');
        }

        $transaction = PricelistPrefixPriceHistory::getDb()->beginTransaction();
        try {
            $dataBefore = json_decode($item->data_before, true);
            $count = count($dataBefore);

            $currentData = PricelistPrefixPrice::find()
                ->where(['pricelist_filter_b_id' => $item->pricelist_filter_b_id])
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
