<?php

namespace app\controllers;

use app\classes\BaseController;
use app\models\billing_uu\PricelistPrefixPrice;
use Yii;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;

class ImporterController extends BaseController
{
    const COMMENT = 6;
    const CACHE_TIMEOUT = 3600;

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

                $oldItems = $this->getOldItems($id, $prefixDateStart);
                $oldItemsKeyValue = $this->groupOldItemsByPrefix($oldItems);

                $historyObject = $historyObjectList[$prefixDateStart];
                $historyItemsIds[] = $historyObject->id;

                $historyObject->date_to = '3000-01-01';
                $historyObject->fillDataBefore();
                $oldItemsHistory[$historyObject->id] = [];

                list($finalPrefixesList, $skippedPrefixesList, $oldItemsIds, $historyItems) = $this->processPrefixesToSave($prefixesToSaveList, $oldItemsKeyValue, $historyObject->id);

                $historyObject->total_count = count($finalPrefixesList);
                $historyObject->save();

                $this->updateOldItemsDateTo($oldItemsIds, $prefixDateStart, $historyObject->id);
                $this->updateSkippedItemsHistory($skippedPrefixesList, $historyObject->id);
                $this->batchInsertNewItems($finalPrefixesList);

                if (!empty($oldItemsIds)) {
                    $oldItemsIdsByDate[] = $oldItemsIds;
                }
            }

            if ($is_replace) {
                $this->handleReplaceMode($id, $maxDateStart, $historyItemsIds, $oldItemsIdsByDate);
            }

            \Yii::$app->db->createCommand()->batchInsert(
                'billing_uu.pricelist_prefix_price_history_item',
                ['pricelist_prefix_price_history_id', 'prefix_b', 'price_old', 'price_new', 'date_from', 'date_to', 'type'],
                $historyItems
            )->execute();

            $transaction->commit();

            $endTime = microtime(true);

            return $this->render('import', [
                'delta_time' => round($endTime - $startTime, 2)
            ]);
        } catch (\yii\db\Exception $e) {
            $transaction->rollBack();

            if ($e->errorInfo[0] == '40001') { // Код ошибки serialization_failure в PostgreSQL
                $attempt++;
                if ($attempt >= $maxAttempts) {
                    throw new \Exception("Deadlock occurred and all attempts to resolve it failed");
                }
                usleep(100000); 
            } else {
                throw $e; // Если ошибка не связана с deadlock, выбрасываем её
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}

    private function getOldItems($id, $prefixDateStart)
    {
        $sql = "
            SELECT id, prefix_b, b_number_price, date_to
            FROM billing_uu.pricelist_prefix_price
            WHERE pricelist_filter_b_id = :id AND date_to > :date_to
            FOR UPDATE
        ";
        
        return Yii::$app->db->createCommand($sql)
            ->bindValue(':id', $id)
            ->bindValue(':date_to', $prefixDateStart)
            ->queryAll();
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
        if (!empty($oldItemsIds)) {
            \Yii::$app->db->createCommand()->update(
                'billing_uu.pricelist_prefix_price',
                ['date_to' => $prefixDateStart, 'history_id' => $historyObjectId],
                new Expression('id in (' . implode(',', $oldItemsIds) . ')')
            )->execute();
        }
    }

    private function updateSkippedItemsHistory($skippedPrefixesList, $historyObjectId)
    {
        if (!empty($skippedPrefixesList)) {
            \Yii::$app->db->createCommand()->update(
                'billing_uu.pricelist_prefix_price',
                ['history_id' => $historyObjectId],
                new Expression('id in (' . implode(',', $skippedPrefixesList) . ')')
            )->execute();
        }
    }

    private function batchInsertNewItems($finalPrefixesList)
    {
        if (!empty($finalPrefixesList)) {
            \Yii::$app->db->createCommand()->batchInsert(
                'billing_uu.pricelist_prefix_price',
                ['pricelist_filter_b_id', 'prefix_b', 'b_number_price', 'date_from', 'date_to', 'history_id'],
                array_map(function ($item) {
                    $item[4] = isset($item[4]) ? $item[4] : '3000-01-01';
                    return $item;
                }, $finalPrefixesList)
            )->execute();
        }
    }

    private function handleReplaceMode($id, $maxDateStart, $historyItemsIds, $oldItemsIdsByDate)
    {
        $historyWhere = new \yii\db\Expression('history_id is null or history_id not in (' . implode(',', $historyItemsIds) . ')');
        $dataRemovedQuery = PricelistPrefixPrice::find()
            ->where(['pricelist_filter_b_id' => $id])
            ->andWhere('date_to > :maxDateStart', [':maxDateStart' => $maxDateStart])
            ->andWhere($historyWhere);

        if (!empty($oldItemsIdsByDate)) {
            foreach ($oldItemsIdsByDate as $oldItemsIds) {
                if (!empty($oldItemsIds)) {
                    $dataRemovedQuery->andWhere(new Expression('id not in (' . implode(',', $oldItemsIds) . ')'));
                }
            }
        }

        $dataRemoved = $dataRemovedQuery->all();
        $removedItemIds = [];

        foreach ($dataRemoved as $removedItem) {
            $historyItems[] = [
                $historyObject->id, $removedItem->prefix_b, $removedItem->b_number_price, '', $removedItem->date_from, $maxDateStart, 'delete'
            ];
            $removedItemIds[] = $removedItem->id;
        }

        if (!empty($removedItemIds)) {
            \Yii::$app->db->createCommand()->update(
                'billing_uu.pricelist_prefix_price',
                ['date_to' => $maxDateStart, 'history_id' => $historyObject->id],
                new Expression('id in (' . implode(',', $removedItemIds) . ')')
            )->execute();
        }
    }
}
