<?php

namespace app\controllers;

use app\classes\BaseController;
use app\exceptions\ModelValidationException;
use app\models\billing_uu\A2pAlphaNum;
use app\models\billing_uu\A2pAlphaNumHistoryItem;
use app\models\billing_uu\PricelistPrefixPrice;
use Exception;
use Yii;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;

class ImporterController extends BaseController
{
    const COMMENT = 6;

    public function actionImport($id, $key, $is_replace)
    {
        if (!\Yii::$app->user->can('pricelist_edit') && !\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $is_replace = ($is_replace === 'true');

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $startTime = microtime(true);
        $prefixesToSave = Yii::$app->cache->get($key);
        $historyObjectList = Yii::$app->cache->get($key . '_history');

        $maxDateStart = date('Y-m-d');

        foreach ($prefixesToSave as $prefixDateStart => $prefixesToSaveList) {
            if ($maxDateStart < $prefixDateStart) {
                $maxDateStart = $prefixDateStart;
            }

            $oldItems = PricelistPrefixPrice::find()
                ->select(['id', 'prefix_b', 'b_number_price', 'date_to'])
                ->where(['pricelist_filter_b_id' => $id])
                ->andWhere('date_to > :date_to')
                ->addParams(['date_to' => $prefixDateStart])
                ->asArray()
                ->all();

            $oldItemsKeyValue = [];

            foreach ($oldItems as $oldItemObject) {
                if (!isset($oldItemsKeyValue[$oldItemObject['prefix_b']])) {
                    $oldItemsKeyValue[$oldItemObject['prefix_b']] = [];
                }

                $oldItemsKeyValue[$oldItemObject['prefix_b']][] = $oldItemObject;
            }

            unset($oldItems);

            $historyObject = $historyObjectList[$prefixDateStart];
            $historyItemsIds[] = $historyObject->id;

            $historyObject->date_to = '3000-01-01'; //$dateEnd;
            $historyObject->fillDataBefore();
            $oldItemsHistory[$historyObject->id] = [];

            $count = 0;
            $finalPrefixesList = [];
            $skippedPrefixesList = [];

            foreach ($prefixesToSaveList as $prefixToSave) {
                $oldPrefixItems = isset($oldItemsKeyValue[$prefixToSave[1]]) ? $oldItemsKeyValue[$prefixToSave[1]] : [];
                $updateOldResult = PricelistPrefixPrice::updateOldWithHistory($prefixToSave, $historyObject->id, $oldPrefixItems);

                if ($updateOldResult[self::COMMENT] != PricelistPrefixPrice::IMPORT_STATUS_SKIPPED) {
                    $count += 1;
                    $finalPrefixesList[] = $prefixToSave;
                    
                    if (!isset($oldItemsIds[$prefixDateStart])) {
                        $oldItemsIds[$prefixDateStart] = [];
                    }
    
                    foreach ($oldPrefixItems as $oldPrefixItem) {
                        $oldItemsIds[$prefixDateStart][] = $oldPrefixItem['id'];
                    }
                } else {
                    if (!isset($skippedPrefixesList[$prefixDateStart])) {
                        $skippedPrefixesList[$prefixDateStart] = [];
                    }
    
                    foreach ($oldPrefixItems as $oldPrefixItem) {
                        $skippedPrefixesList[$prefixDateStart][] = $oldPrefixItem['id'];
                    }
                }

                $historyItems[] = $updateOldResult;
            }

            $historyObject->total_count = $count;
            $historyObject->save();

            if (!empty($oldItemsIds[$prefixDateStart])) {
                \Yii::$app->db->createCommand()->update(
                    'billing_uu.pricelist_prefix_price',
                    ['date_to' => $prefixDateStart, 'history_id' => $historyObject->id],
                    new Expression('id in (' . implode(',', $oldItemsIds[$prefixDateStart]) . ')')
                )->execute();
            }
            
            if (!empty($skippedPrefixesList[$prefixDateStart])) {
                \Yii::$app->db->createCommand()->update(
                    'billing_uu.pricelist_prefix_price',
                    ['history_id' => $historyObject->id],
                    new Expression('id in (' . implode(',', $skippedPrefixesList[$prefixDateStart]) . ')')
                )->execute();
            }

            \Yii::$app->db->createCommand()->batchInsert(
                'billing_uu.pricelist_prefix_price',
                ['pricelist_filter_b_id', 'prefix_b', 'b_number_price', 'date_from', 'date_to', 'history_id'],
                $finalPrefixesList
            )->execute();
        }

        unset($prefixesToSave);
        unset($prefixesToSaveList);
        unset($finalPrefixesList);

        if ($is_replace) {
            $historyWhere = new \yii\db\Expression('history_id is null or history_id not in (' . implode(',', $historyItemsIds) . ')');

            $dataRemovedQuery = PricelistPrefixPrice::find()
                ->where(['pricelist_filter_b_id' => $id])
                ->andWhere('date_to > \'' . $maxDateStart . '\'')
                ->andWhere($historyWhere);

            if (!empty($oldItemsIds)) {
                foreach ($oldItemsIds as $oldItemsIdsByDate) {
                    if (!empty($oldItemsIdsByDate)) {
                        $dataRemovedQuery->andWhere('id not in (' . implode(',', $oldItemsIdsByDate) . ')');
                    }
                }
            }

            $dataRemoved = $dataRemovedQuery->all();
            $removedItemIds = [];

            foreach ($dataRemoved as $removedItem) {
                $historyItems[] = [
                    $historyObject->id,
                    $removedItem->prefix_b,
                    $removedItem->b_number_price,
                    '',
                    $removedItem->date_from,
                    $maxDateStart,
                    'delete'
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

        \Yii::$app->db->createCommand()->batchInsert(
            'billing_uu.pricelist_prefix_price_history_item',
            ['pricelist_prefix_price_history_id', 'prefix_b', 'price_old', 'price_new', 'date_from', 'date_to', 'type'],
            $historyItems
        )->execute();

        $endTime = microtime(true);

        return $this->render('import', [
            'delta_time' => round($endTime - $startTime, 2)
        ]);
    }

    public function actionImportAlphaNumbers($id, $key, $is_replace)
    {
        if (!\Yii::$app->user->can('pricelist_edit') && !\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $is_replace = ($is_replace === 'true');

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $startTime = microtime(true);
        $alphaNumsToSave = Yii::$app->cache->get($key);
        $historyObjectList = Yii::$app->cache->get($key . '_history');
       
        $finalAlphaList = [];
        $oldItems = A2pAlphaNum::find()
                ->select(['id', 'alphanum'])
                ->where(['pricelist_filter_a_id' => $id])
                ->asArray()
                ->all();

        $historyItemsIds = [];
        foreach ($alphaNumsToSave as $alphaNumsToSaveList) {
            $oldItemsKeyValue = [];

            foreach ($oldItems as $oldItemObject) {
                if (!isset($oldItemsKeyValue[$oldItemObject['alphanum']])) {
                    $oldItemsKeyValue[$oldItemObject['alphanum']] = [];
                }

                $oldItemsKeyValue[$oldItemObject['alphanum']][] = $oldItemObject;
            }

            $historyObject = $historyObjectList[0];
            $historyItemsIds[] = $historyObject->id;

            $historyObject->fillDataBefore();
            $oldItemsHistory[$historyObject->id] = [];

            $count = 0;

            $oldAlphaItems = isset($oldItemsKeyValue[$alphaNumsToSaveList[1]]) ? $oldItemsKeyValue[$alphaNumsToSaveList[1]] : [];
            $updateOldResult = A2pAlphaNum::updateOldWithHistory($alphaNumsToSaveList, $historyObject->id, $oldAlphaItems);
            $count = count($alphaNumsToSave);
            $finalAlphaList[] = $alphaNumsToSaveList;

            $updateOldResult = [$updateOldResult[0], $updateOldResult[1][1], isset($updateOldResult[2]) && $updateOldResult[2] ? $updateOldResult[2] : null];
            $historyItems[] = $updateOldResult;

            $historyObject->total_count = $count;

            if (!$historyObject->save()) {
                throw new ModelValidationException($historyObject);
            }
            
        }

        if ($finalAlphaList) {
            \Yii::$app->db->createCommand()->batchInsert(
                A2pAlphaNum::tableName(),
                ['pricelist_filter_a_id', 'alphanum','history_id'],
                $finalAlphaList
            )->execute();    
        }

        unset($oldItems);
        unset($alphaNumsToSave);
        unset($alphaNumsToSaveList);
        unset($finalAlphaList);

        if ($is_replace) {
            $historyWhere = new \yii\db\Expression('history_id is null or history_id not in (' . implode(',', $historyItemsIds) . ')');

            $dataRemovedQuery = A2pAlphaNum::find()
                ->where(['pricelist_filter_a_id' => $id])
                ->andWhere($historyWhere);

            $dataRemoved = $dataRemovedQuery->all();
            $removedItemIds = [];

            foreach ($dataRemoved as $removedItem) {
                $historyItems[] = [
                    $historyObject->id,
                    $removedItem->alphanum,
                    'delete'
                ];

                $removedItemIds[] = $removedItem->id;
            }

            if (!empty($removedItemIds)) {
                \Yii::$app->db->createCommand()->update(
                    A2pAlphaNum::tableName(),
                    ['history_id' => $historyObject->id],
                    ['id' => $removedItemIds]
                )->execute();
            }
        }
        
        if ($historyItems) {
            \Yii::$app->db->createCommand()->batchInsert(
                A2pAlphaNumHistoryItem::tableName(),
                ['a2p_alphanum_history_id', 'alphanum', 'type'],
                $historyItems
            )->execute();    
        }

        $endTime = microtime(true);

        return $this->render('import', [
            'delta_time' => round($endTime - $startTime, 2)
        ]);
    }
}
