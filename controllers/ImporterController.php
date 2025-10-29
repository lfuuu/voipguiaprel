<?php

namespace app\controllers;

use app\classes\BaseController;
use app\models\billing_uu\PricelistPrefixPrice;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\web\ForbiddenHttpException;

class ImporterController extends BaseController
{
    const COMMENT = 6;
    const CACHE_TIMEOUT = 3600;
    private const PG_MAX_PARAMS = 65000;

    /** Безопасный batchInsert с порционированием по лимиту параметров */
    private function safeBatchInsert(string $table, array $columns, array $rows): void
    {
        if (empty($rows)) return;

        $colsCount = max(1, count($columns));
        $maxRowsPerStmt = max(1, (int) floor(self::PG_MAX_PARAMS / $colsCount));

        foreach (array_chunk($rows, $maxRowsPerStmt) as $chunk) {
            Yii::$app->db->createCommand()->batchInsert($table, $columns, $chunk)->execute();
        }
    }

    /** Безопасный update по массиву id */
    private function safeUpdateByIds(string $table, array $set, array $ids, int $preferChunk = 10000): void
    {
        if (empty($ids)) return;

        $hardLimit = max(1, self::PG_MAX_PARAMS - count($set) - 10);
        $chunkSize = min($preferChunk, $hardLimit);

        foreach (array_chunk($ids, $chunkSize) as $chunk) {
            Yii::$app->db->createCommand()->update($table, $set, ['id' => $chunk])->execute();
        }
    }

    public function actionImport($id, $key, $is_replace)
    {
        if (!\Yii::$app->user->can('pricelist_edit') && !\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $is_replace = ($is_replace === 'true');
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $maxAttempts = 5;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $transaction = Yii::$app->db->beginTransaction();

            try {
                $startTime = microtime(true);
                $prefixesToSave = Yii::$app->cache->get($key);
                $historyObjectList = Yii::$app->cache->get($key . '_history');
                $maxDateStart = date('Y-m-d');

                $historyItems = [];
                $oldItemsIdsByDate = [];

                foreach ($prefixesToSave as $prefixDateStart => $prefixesToSaveList) {
                    if ($maxDateStart < $prefixDateStart) {
                        $maxDateStart = $prefixDateStart;
                    }

                    $prefixes = array_column($prefixesToSaveList, 1);
                    $oldItems = $this->getOldItems($id, $prefixDateStart, $prefixes);
                    $oldItemsKeyValue = $this->groupOldItemsByPrefix($oldItems);

                    $historyObject = $historyObjectList[$prefixDateStart];
                    $historyItemsIds[] = $historyObject->id;

                    $historyObject->date_to = '3000-01-01';
                    $historyObject->fillDataBefore();
                    $oldItemsHistory[$historyObject->id] = [];

                    list($finalPrefixesList, $skippedPrefixesList, $oldItemsIds, $historyItemsPart) =
                        $this->processPrefixesToSave($prefixesToSaveList, $oldItemsKeyValue, $historyObject->id);

                    $historyObject->total_count = count($finalPrefixesList);
                    $historyObject->save();

                    $this->updateOldItemsDateTo($oldItemsIds, $prefixDateStart, $historyObject->id);
                    $this->updateSkippedItemsHistory($skippedPrefixesList, $historyObject->id);
                    $this->batchInsertNewItems($finalPrefixesList);

                    $historyItems = array_merge($historyItems, $historyItemsPart);

                    if (!empty($oldItemsIds)) {
                        $oldItemsIdsByDate[] = $oldItemsIds;
                    }
                }

                if ($is_replace) {
                    $this->handleReplaceMode($id, $maxDateStart, $historyItemsIds, $historyItems);
                }

                $this->safeBatchInsert(
                    'billing_uu.pricelist_prefix_price_history_item',
                    ['pricelist_prefix_price_history_id', 'prefix_b', 'price_old', 'price_new', 'date_from', 'date_to', 'type'],
                    $historyItems
                );

                $transaction->commit();

                $endTime = microtime(true);

                return $this->render('import', [
                    'delta_time' => round($endTime - $startTime, 2)
                ]);
            } catch (\yii\db\Exception $e) {
                $transaction->rollBack();

                if ($e->errorInfo[0] == '40001') {
                    $attempt++;
                    if ($attempt >= $maxAttempts) {
                        throw new \Exception("Deadlock occurred and all attempts to resolve it failed");
                    }
                    usleep(100000);
                } else {
                    throw $e;
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }
    }

    private function getOldItems($id, $prefixDateStart, $prefixes)
    {
        $result = [];

        if (empty($prefixes)) {
            return $result;
        }

        $chunkSize = 5000;

        foreach (array_chunk($prefixes, $chunkSize) as $chunk) {
            $placeholders = [];
            $params = [':id' => $id, ':date_to' => $prefixDateStart];

            foreach ($chunk as $i => $p) {
                $ph = ':p' . $i;
                $placeholders[] = $ph;
                $params[$ph] = $p;
            }

            $sql = "
                SELECT id, prefix_b, b_number_price, date_to
                FROM billing_uu.pricelist_prefix_price
                WHERE pricelist_filter_b_id = :id
                  AND date_to > :date_to
                  AND prefix_b IN (" . implode(',', $placeholders) . ")
            ";

            $res = Yii::$app->db->createCommand($sql, $params)->queryAll();
            if (!empty($res)) {
                $result = array_merge($result, $res);
            }
        }

        return $result;
    }

    private function groupOldItemsByPrefix($oldItems)
    {
        $oldItemsKeyValue = [];
        foreach ($oldItems as $oldItemObject) {
            $oldItemsKeyValue[$oldItemObject['prefix_b']][] = $oldItemObject;
        }
        return $oldItemsKeyValue;
    }

    private function processPrefixesToSave($prefixesToSaveList, $oldItemsKeyValue, $historyObjectId)
    {
        $finalPrefixesList = [];
        $skippedPrefixesList = [];
        $oldItemsIds = [];
        $historyItems = [];

        foreach ($prefixesToSaveList as $prefixToSave) {
            $oldPrefixItems = $oldItemsKeyValue[$prefixToSave[1]] ?? [];
            $updateOldResult = PricelistPrefixPrice::updateOldWithHistory($prefixToSave, $historyObjectId, $oldPrefixItems);

            if ($updateOldResult[self::COMMENT] != PricelistPrefixPrice::IMPORT_STATUS_SKIPPED) {
                $finalPrefixesList[] = $prefixToSave;

                foreach ($oldPrefixItems as $oldPrefixItem) {
                    $oldItemsIds[] = $oldPrefixItem['id'];
                }
            } else {
                foreach ($oldPrefixItems as $oldPrefixItem) {
                    $skippedPrefixesList[] = $oldPrefixItem['id'];
                }
            }
            $historyItems[] = $updateOldResult;
        }

        return [$finalPrefixesList, $skippedPrefixesList, $oldItemsIds, $historyItems];
    }

    private function updateOldItemsDateTo($oldItemsIds, $prefixDateStart, $historyObjectId)
    {
        if (empty($oldItemsIds)) return;
        $this->safeUpdateByIds(
            'billing_uu.pricelist_prefix_price',
            ['date_to' => $prefixDateStart, 'history_id' => $historyObjectId],
            $oldItemsIds,
            10000
        );
    }

    private function updateSkippedItemsHistory($skippedPrefixesList, $historyObjectId)
    {
        if (empty($skippedPrefixesList)) return;

        $this->safeUpdateByIds(
            'billing_uu.pricelist_prefix_price',
            ['history_id' => $historyObjectId],
            $skippedPrefixesList,
            10000
        );
    }

    private function batchInsertNewItems($finalPrefixesList)
    {
        if (empty($finalPrefixesList)) return;

        $this->safeBatchInsert(
            'billing_uu.pricelist_prefix_price',
            ['pricelist_filter_b_id', 'prefix_b', 'b_number_price', 'date_from', 'date_to', 'history_id'],
            array_map(function ($item) {
                $item[4] = isset($item[4]) ? $item[4] : '3000-01-01';
                return $item;
            }, $finalPrefixesList)
        );
    }

    private function handleReplaceMode($id, $maxDateStart, $historyItemsIds, &$historyItems)
    {
        $chunk = 5000;
        $historyIdForDeletes = !empty($historyItemsIds) ? end($historyItemsIds) : null;

        $baseWhere = ['and',
            ['pricelist_filter_b_id' => $id],
            ['>', 'date_to', $maxDateStart],
            empty($historyItemsIds)
                ? 'history_id IS NULL'
                : ['or', 'history_id IS NULL', ['not in', 'history_id', $historyItemsIds]],
        ];

        while (true) {
            $rows = (new Query())
                ->select(['id', 'prefix_b', 'b_number_price', 'date_from'])
                ->from('billing_uu.pricelist_prefix_price')
                ->where($baseWhere)
                ->limit($chunk)
                ->all();

            if (empty($rows)) {
                break;
            }

            $ids = array_column($rows, 'id');

            foreach ($rows as $r) {
                $historyItems[] = [
                    $historyIdForDeletes,
                    $r['prefix_b'],
                    $r['b_number_price'],
                    '',
                    $r['date_from'],
                    $maxDateStart,
                    'delete'
                ];
            }

            $this->safeUpdateByIds(
                'billing_uu.pricelist_prefix_price',
                ['date_to' => $maxDateStart, 'history_id' => $historyIdForDeletes],
                $ids,
                10000
            );
        }
    }
}
