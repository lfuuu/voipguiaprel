<?php

namespace app\controllers;

use app\classes\BaseController;
use app\models\billing_uu\PricelistPrefixPrice;
use app\models\billing_uu\PricelistPrefixPriceHistory;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\web\ForbiddenHttpException;

class ImporterController extends BaseController
{
    const COMMENT = 6;
    const CACHE_TIMEOUT = 3600;
    private const BATCH_LIMIT = 500;

    public function actionImport($id, $key, $is_replace)
    {
        if (!Yii::$app->user->can('pricelist_edit') && !Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $is_replace = ($is_replace === 'true');
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
                $historyItemsIds = [];
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

                    [$finalPrefixesList, $skippedPrefixesList, $oldItemsIds, $localHistoryItems] =
                        $this->processPrefixesToSave($prefixesToSaveList, $oldItemsKeyValue, $historyObject->id);

                    $historyObject->total_count = count($finalPrefixesList);
                    $historyObject->save();

                    $this->updateOldItemsDateTo($oldItemsIds, $prefixDateStart, $historyObject->id);
                    $this->updateSkippedItemsHistory($skippedPrefixesList, $historyObject->id);
                    $this->batchInsertNewItems($finalPrefixesList);

                    $historyItems = array_merge($historyItems, $localHistoryItems);

                    if (!empty($oldItemsIds)) {
                        $oldItemsIdsByDate[] = $oldItemsIds;
                    }
                }

                if ($is_replace) {
                    $this->handleReplaceMode($id, $maxDateStart, $historyItemsIds, $oldItemsIdsByDate);
                }

                if ($historyItems) {
                    Yii::$app->db->createCommand()->batchInsert(
                        'billing_uu.pricelist_prefix_price_history_item',
                        ['pricelist_prefix_price_history_id', 'prefix_b', 'price_old', 'price_new', 'date_from', 'date_to', 'type'],
                        $historyItems
                    )->execute();
                }

                $transaction->commit();

                $endTime = microtime(true);

                return $this->render('import', [
                    'delta_time' => round($endTime - $startTime, 2)
                ]);
            } catch (\yii\db\Exception $e) {
                $transaction->rollBack();
                $pgCode = $e->errorInfo[0] ?? null;
                if (in_array($pgCode, ['40001', '40P01'], true)) {
                    // deadlock or serialization failure
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

    private function getOldItems($id, $prefixDateStart, array $prefixes)
    {
        return (new Query())
            ->select(['id', 'prefix_b', 'b_number_price', 'date_to'])
            ->from('billing_uu.pricelist_prefix_price')
            ->where(['pricelist_filter_b_id' => $id])
            ->andWhere(['>', 'date_to', $prefixDateStart])
            ->andWhere(['in', 'prefix_b', $prefixes])
            ->all();
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

    private function updateOldItemsDateTo(array $oldItemsIds, $prefixDateStart, $historyObjectId)
    {
        if (!$oldItemsIds) {
            return;
        }

        Yii::$app->db->createCommand()->update(
            'billing_uu.pricelist_prefix_price',
            [
                'date_to' => $prefixDateStart,
                'history_id' => $historyObjectId
            ],
            ['id' => $oldItemsIds]
        )->execute();
    }

    private function updateSkippedItemsHistory(array $skippedPrefixesList, $historyObjectId)
    {
        if (!$skippedPrefixesList) {
            return;
        }

        Yii::$app->db->createCommand()->update(
            'billing_uu.pricelist_prefix_price',
            ['history_id' => $historyObjectId],
            ['id' => $skippedPrefixesList]
        )->execute();
    }

    private function batchInsertNewItems(array $finalPrefixesList)
    {
        if (!$finalPrefixesList) {
            return;
        }

        foreach (array_chunk($finalPrefixesList, self::BATCH_LIMIT) as $chunk) {
            Yii::$app->db->createCommand()->batchInsert(
                'billing_uu.pricelist_prefix_price',
                ['pricelist_filter_b_id', 'prefix_b', 'b_number_price', 'date_from', 'date_to', 'history_id'],
                array_map(function ($item) {
                    $item[4] = $item[4] ?? '3000-01-01';
                    return $item;
                }, $chunk)
            )->execute();
        }
    }

    private function handleReplaceMode($id, $maxDateStart, $historyItemsIds, $oldItemsIdsByDate)
    {
        $historyId = null;
        $historyModel = new PricelistPrefixPriceHistory([
            'pricelist_filter_b_id' => $id,
            'date_from' => $maxDateStart,
            'date_to' => '3000-01-01',
            'total_count' => 0,
            'comment' => 'auto-delete by replace mode'
        ]);
        if ($historyModel->save(false)) {
            $historyId = $historyModel->id;
        }

        $query = PricelistPrefixPrice::find()
            ->where(['pricelist_filter_b_id' => $id])
            ->andWhere(['>', 'date_to', $maxDateStart]);

        if ($historyItemsIds) {
            $query->andWhere(['not in', 'history_id', $historyItemsIds]);
        }

        if ($oldItemsIdsByDate) {
            foreach ($oldItemsIdsByDate as $ids) {
                if ($ids) {
                    $query->andWhere(['not in', 'id', $ids]);
                }
            }
        }

        $removedRows = $query->all();

        $removedItemIds = [];
        foreach ($removedRows as $removedItem) {
            $removedItemIds[] = $removedItem->id;
        }

        if ($removedItemIds) {
            Yii::$app->db->createCommand()->update(
                'billing_uu.pricelist_prefix_price',
                ['date_to' => $maxDateStart, 'history_id' => $historyId],
                ['id' => $removedItemIds]
            )->execute();
        }
    }
}
