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
        if (!$item) {
            throw new Exception('Запись истории не существует');
        }

        $dataBefore = json_decode($item->data_before, true) ?: [];
        if (empty($dataBefore)) {
            $item->delete();
            $delta = round(microtime(true) - $startTime, 2);
            return $this->render('reset', [
                'delta_time' => $delta,
                'message'    => 'Пустой импорт: откат не требуется',
            ]);
        }

        $transaction = PricelistPrefixPriceHistory::getDb()->beginTransaction();
        try {

            $currentData = PricelistPrefixPrice::find()
                ->where(['pricelist_filter_b_id' => $item->pricelist_filter_b_id])
                ->indexBy('id')
                ->asArray()
                ->all();

            $dataToUpdateHistory = [];
            $dataToDelete = [];
            $dataToInsert = [];
            $beforeIds = [];

            foreach ($dataBefore as $beforeRecord) {
                $beforeId = $beforeRecord['id'];
                $beforeIds[] = $beforeId;

                if (!isset($currentData[$beforeId])) {
                    $dataToInsert[] = [
                        $beforeRecord['id'],
                        $beforeRecord['pricelist_filter_b_id'],
                        $beforeRecord['prefix_b'],
                        $beforeRecord['b_number_price'],
                        $beforeRecord['change_flag'],
                        $beforeRecord['date_from'],
                        $beforeRecord['date_to'],
                        $beforeRecord['b_number_connect_price'],
                        $beforeRecord['history_id'],
                    ];
                } else {
                    $current = $currentData[$beforeId];
                    if (
                        $current['prefix_b'] != $beforeRecord['prefix_b'] ||
                        $current['b_number_price'] != $beforeRecord['b_number_price'] ||
                        $current['change_flag'] != $beforeRecord['change_flag'] ||
                        $current['date_from'] != $beforeRecord['date_from'] ||
                        $current['date_to'] != $beforeRecord['date_to'] ||
                        $current['b_number_connect_price'] != $beforeRecord['b_number_connect_price']
                    ) {
                        $dataToDelete[] = $beforeId;
                        $dataToInsert[] = [
                            $beforeRecord['id'],
                            $beforeRecord['pricelist_filter_b_id'],
                            $beforeRecord['prefix_b'],
                            $beforeRecord['b_number_price'],
                            $beforeRecord['change_flag'],
                            $beforeRecord['date_from'],
                            $beforeRecord['date_to'],
                            $beforeRecord['b_number_connect_price'],
                            $beforeRecord['history_id'],
                        ];
                    } elseif ($current['history_id'] != $beforeRecord['history_id']) {
                        $dataToUpdateHistory[$beforeRecord['history_id']][] = $beforeId;
                    }
                    unset($currentData[$beforeId]);
                }
            }

            $toRemove = array_diff(array_keys($currentData), $beforeIds);
            $dataToDelete = array_merge($dataToDelete, $toRemove);

            foreach (array_chunk($dataToDelete, self::CHUNK_SIZE_DELETE) as $chunk) {
                PricelistPrefixPrice::deleteAll(['id' => $chunk]);
            }

            foreach ($dataToUpdateHistory as $historyId => $ids) {
                foreach (array_chunk($ids, self::CHUNK_SIZE_UPDATE) as $chunk) {
                    \Yii::$app->db->createCommand()
                        ->update(
                            PricelistPrefixPrice::tableName(),
                            ['history_id' => $historyId],
                            ['id' => $chunk]
                        )
                        ->execute();
                }
            }

            foreach (array_chunk($dataToInsert, self::CHUNK_SIZE_INSERT, true) as $chunk) {
                \Yii::$app->db->createCommand()
                    ->batchInsert(
                        PricelistPrefixPrice::tableName(),
                        [
                            'id',
                            'pricelist_filter_b_id',
                            'prefix_b',
                            'b_number_price',
                            'change_flag',
                            'date_from',
                            'date_to',
                            'b_number_connect_price',
                            'history_id',
                        ],
                        $chunk
                    )
                    ->execute();
            }

            $item->delete();

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive()) {
                $transaction->rollBack();
            }
        }

        $delta = round(microtime(true) - $startTime, 2);
        return $this->render('reset', [
            'delta_time' => $delta,
        ]);
    }
}
