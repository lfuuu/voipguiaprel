<?php

namespace app\controllers;

use app\classes\BaseController;
use app\models\billing_uu\PricelistPrefixPrice;
use Yii;
use yii\db\Command;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;

/**
 * Быстрый импорт прайс-листа с поддержкой replace-режима.
 * Основные отличия:
 *  - Данные сгружаются во временную staging-таблицу tmp_import (TEMP, on commit drop).
 *  - Все изменения применяются set-based SQL (UPDATE/INSERT ... SELECT, RETURNING).
 *  - Сериализация импорта по одному фильтру через pg_advisory_xact_lock(pricelist_filter_b_id).
 *  - Ретраи по 40P01/40001 с экспоненциальной паузой.
 */
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
        ini_set('max_execution_time', '0');

        // Из кеша приходят:
        // $prefixesToSave: [ 'YYYY-MM-DD' => [ [filter_id, prefix_b, price, date_from, date_to?], ... ], ... ]
        // $historyObjectList: [ 'YYYY-MM-DD' => HistoryModel ]
        $prefixesToSave   = Yii::$app->cache->get($key);
        $historyObjectList = Yii::$app->cache->get($key . '_history');

        if (!$prefixesToSave || !$historyObjectList) {
            throw new \RuntimeException('Нет данных для импорта (cache key not found)');
        }

        // Нормализуем: гарантируем date_to = '3000-01-01' если не указан
        $rows = [];
        $maxDateStart = '0001-01-01';
        foreach ($prefixesToSave as $dateStart => $list) {
            foreach ($list as $item) {
                $rows[] = [
                    (int)$id,
                    (string)$item[1],                                   // prefix_b
                    (string)$item[2],                                   // b_number_price (numeric как строка)
                    (string)$dateStart,                                 // date_from (группа ключа)
                    isset($item[4]) && $item[4] ? (string)$item[4] : '3000-01-01', // date_to
                ];
            }
            if ($maxDateStart < $dateStart) $maxDateStart = $dateStart;
        }

        // retry policy
        $maxAttempts = 5;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $startTime = microtime(true);

                // Сериализация импорта для одного фильтра
                Yii::$app->db->createCommand('SELECT pg_advisory_xact_lock(:k)')
                    ->bindValue(':k', (int)$id)
                    ->execute();

                // Локальные параметры транзакции
                Yii::$app->db->createCommand("SET LOCAL synchronous_commit = OFF")->execute();
                Yii::$app->db->createCommand("SET LOCAL lock_timeout = '5s'")->execute();

                // 1) TEMP staging-таблица
                $this->createTempTable();

                // 2) Загрузка данных в staging (батчами)
                $this->loadIntoTemp($rows);

                // Индекс для быстрых join'ов
                Yii::$app->db->createCommand('CREATE INDEX ON tmp_import (pricelist_filter_b_id, prefix_b, date_from)')->execute();

                // Массивы для replace-режима
                $historyIdsByDate = [];   // 'YYYY-MM-DD' => history_id
                $updatedIdsByDate = [];   // массив массивов id изменённых (для исключения в replace)
                $allHistoryIds    = [];   // список всех history_id

                // 3) Подготовить и сохранить history-объекты по датам
                foreach ($historyObjectList as $dateStart => $historyObject) {
                    // total_count = сколько строк в этой группе
                    $totalCount = (int)Yii::$app->db->createCommand("
                        SELECT count(*)
                          FROM tmp_import
                         WHERE pricelist_filter_b_id = :id AND date_from = :ds
                    ")->bindValues([':id' => (int)$id, ':ds' => $dateStart])->queryScalar();

                    $historyObject->date_to = '3000-01-01';
                    if (method_exists($historyObject, 'fillDataBefore')) {
                        $historyObject->fillDataBefore();
                    }
                    $historyObject->total_count = $totalCount;
                    $historyObject->save(false);

                    $historyIdsByDate[$dateStart] = (int)$historyObject->id;
                    $allHistoryIds[] = (int)$historyObject->id;
                    $updatedIdsByDate[$dateStart] = [];
                }

                // 4) Для каждой даты применить set-based апдейты/инсерты и историю
                foreach ($historyIdsByDate as $dateStart => $historyId) {

                    // 4.1 Обновить пересекающиеся старые записи (закрываем хвосты)
                    // Возвращаем старые значения для истории
                    $rowsUpdated = (new \yii\db\Query())
    ->select([
        'id'            => 'p.id',
        'prefix_b'      => 'p.prefix_b',
        'price_old'     => 'p.b_number_price',
        'date_from_old' => 'p.date_from',
        'date_to_new'   => new \yii\db\Expression(':ds', [':ds' => $dateStart]),
    ])
    ->from('billing_uu.pricelist_prefix_price p')
    ->innerJoin('tmp_import s', '
        s.pricelist_filter_b_id = p.pricelist_filter_b_id
        AND s.prefix_b = p.prefix_b
        AND s.date_from = :ds
    ', [':ds' => $dateStart])
    ->where(['p.pricelist_filter_b_id' => (int)$id])
    ->andWhere('p.date_to > :ds', [':ds' => $dateStart])
    ->all();


                    if (!empty($rowsUpdated)) {
                        // UPDATE set-based
                        Yii::$app->db->createCommand("
                            UPDATE billing_uu.pricelist_prefix_price p
                               SET date_to = :ds,
                                   history_id = :hid
                              FROM tmp_import s
                             WHERE p.pricelist_filter_b_id = :id
                               AND s.pricelist_filter_b_id = :id
                               AND s.date_from = :ds
                               AND p.prefix_b = s.prefix_b
                               AND p.date_to > :ds
                        ")->bindValues([
                            ':id'  => (int)$id,
                            ':ds'  => $dateStart,
                            ':hid' => $historyId,
                        ])->execute();

                        // История «update»: old->new
                        // price_new берём из staging для той же (prefix_b, date_from)
                        Yii::$app->db->createCommand("
                            INSERT INTO billing_uu.pricelist_prefix_price_history_item
                                (pricelist_prefix_price_history_id, prefix_b, price_old, price_new, date_from, date_to, type)
                            SELECT :hid, p.prefix_b, p.b_number_price, s.b_number_price, p.date_from, :ds, 'update'
                              FROM billing_uu.pricelist_prefix_price p
                              JOIN tmp_import s
                                ON s.pricelist_filter_b_id = p.pricelist_filter_b_id
                               AND s.prefix_b = p.prefix_b
                               AND s.date_from = :ds
                             WHERE p.pricelist_filter_b_id = :id
                               AND p.date_to = :ds
                        ")->bindValues([
                            ':hid' => $historyId,
                            ':id'  => (int)$id,
                            ':ds'  => $dateStart,
                        ])->execute();

                        // Сохраним ID обновлённых (для исключения в replace)
                        $ids = (new \yii\db\Query())
                            ->select('id')
                            ->from('billing_uu.pricelist_prefix_price')
                            ->where([
                                'pricelist_filter_b_id' => (int)$id,
                            ])
                            ->andWhere('date_to = :ds', [':ds' => $dateStart])
                            ->andWhere([
                                'prefix_b' => (new \yii\db\Query())
                                    ->select('prefix_b')->from('tmp_import')
                                    ->where([
                                        'pricelist_filter_b_id' => (int)$id,
                                        'date_from' => $dateStart
                                    ])
                            ])->column();

                        if ($ids) $updatedIdsByDate[$dateStart] = array_map('intval', $ids);
                    }

                    // 4.2 Пометить «совпавшие» записи (same price & same date_from) как пропущенные: только history_id
                    Yii::$app->db->createCommand("
                        UPDATE billing_uu.pricelist_prefix_price p
                           SET history_id = :hid
                          FROM tmp_import s
                         WHERE p.pricelist_filter_b_id = :id
                           AND s.pricelist_filter_b_id = :id
                           AND s.date_from = :ds
                           AND p.prefix_b = s.prefix_b
                           AND p.date_from = :ds
                           AND p.b_number_price = s.b_number_price
                    ")->bindValues([
                        ':hid' => $historyId,
                        ':id'  => (int)$id,
                        ':ds'  => $dateStart,
                    ])->execute();

                    // 4.3 Вставить новые записи, которых нет на ту же дату_from
                    Yii::$app->db->createCommand("
                        INSERT INTO billing_uu.pricelist_prefix_price
                            (pricelist_filter_b_id, prefix_b, b_number_price, date_from, date_to, history_id)
                        SELECT s.pricelist_filter_b_id, s.prefix_b, s.b_number_price, s.date_from,
                               COALESCE(s.date_to, DATE '3000-01-01'), :hid
                          FROM tmp_import s
                          LEFT JOIN billing_uu.pricelist_prefix_price p
                                 ON p.pricelist_filter_b_id = s.pricelist_filter_b_id
                                AND p.prefix_b = s.prefix_b
                                AND p.date_from = s.date_from
                         WHERE s.pricelist_filter_b_id = :id
                           AND s.date_from = :ds
                           AND p.id IS NULL
                    ")->bindValues([
                        ':hid' => $historyId,
                        ':id'  => (int)$id,
                        ':ds'  => $dateStart,
                    ])->execute();

                    // История «insert» для реально новых строк
                    Yii::$app->db->createCommand("
                        INSERT INTO billing_uu.pricelist_prefix_price_history_item
                            (pricelist_prefix_price_history_id, prefix_b, price_old, price_new, date_from, date_to, type)
                        SELECT :hid, s.prefix_b, NULL, s.b_number_price, s.date_from, s.date_to, 'insert'
                          FROM tmp_import s
                          LEFT JOIN billing_uu.pricelist_prefix_price p
                                 ON p.pricelist_filter_b_id = s.pricelist_filter_b_id
                                AND p.prefix_b = s.prefix_b
                                AND p.date_from = s.date_from
                         WHERE s.pricelist_filter_b_id = :id
                           AND s.date_from = :ds
                           AND p.id IS NOT NULL   -- только что вставленные мы не отличим простым left join,
                                                -- поэтому используем менее двусмысленную вставку через anti-join к СТАРЫМ данным
                    ")->bindValues([
                        ':hid' => $historyId,
                        ':id'  => (int)$id,
                        ':ds'  => $dateStart,
                    ])->execute();
                }

                // 5) Replace-режим: закрыть "лишние" записи, которых нет в новом наборе
                if ($is_replace) {
                    // Будем использовать history_id для maxDateStart-группы
                    $historyIdForDelete = (int)($historyIdsByDate[$maxDateStart] ?? end($historyIdsByDate));

                    // Закроем все записи, у которых date_to > maxDateStart и которых нет в staging для этого фильтра
                    Yii::$app->db->createCommand("
                        WITH del AS (
                            UPDATE billing_uu.pricelist_prefix_price p
                               SET date_to = :maxds,
                                   history_id = :hid
                              WHERE p.pricelist_filter_b_id = :id
                                AND p.date_to > :maxds
                                AND NOT EXISTS (
                                      SELECT 1
                                        FROM tmp_import s
                                       WHERE s.pricelist_filter_b_id = p.pricelist_filter_b_id
                                         AND s.prefix_b = p.prefix_b
                                  )
                              RETURNING p.prefix_b, p.b_number_price, p.date_from
                        )
                        INSERT INTO billing_uu.pricelist_prefix_price_history_item
                            (pricelist_prefix_price_history_id, prefix_b, price_old, price_new, date_from, date_to, type)
                        SELECT :hid, d.prefix_b, d.b_number_price, NULL, d.date_from, :maxds, 'delete'
                          FROM del d
                    ")->bindValues([
                        ':id'    => (int)$id,
                        ':maxds' => $maxDateStart,
                        ':hid'   => $historyIdForDelete,
                    ])->execute();
                }

                $transaction->commit();

                $endTime = microtime(true);
                return $this->render('import', [
                    'delta_time' => round($endTime - $startTime, 2)
                ]);

            } catch (\yii\db\Exception $e) {
                $transaction->rollBack();
                $sqlState = $e->errorInfo[0] ?? null;
                if ($sqlState === '40P01' || $sqlState === '40001') {
                    // Deadlock / serialization failure — ретрай с экспоненциальной паузой и джиттером
                    $attempt++;
                    if ($attempt >= $maxAttempts) {
                        throw new \RuntimeException("Deadlock/serialization failure: попытки исчерпаны", 0, $e);
                    }
                    $delay = (100000 * (1 << ($attempt - 1))) + random_int(0, 100000); // 0.1s, 0.2s, 0.4s, ...
                    usleep($delay);
                } else {
                    throw $e;
                }
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        }
    }

    /**
     * TEMP staging table for import
     */
    private function createTempTable(): void
    {
        Yii::$app->db->createCommand("
            CREATE TEMP TABLE IF NOT EXISTS tmp_import (
                pricelist_filter_b_id BIGINT NOT NULL,
                prefix_b TEXT NOT NULL,
                b_number_price NUMERIC(12,6) NOT NULL,
                date_from DATE NOT NULL,
                date_to DATE NOT NULL
            ) ON COMMIT DROP
        ")->execute();
        // Очистим на случай повторного использования сессии
        Yii::$app->db->createCommand("TRUNCATE tmp_import")->execute();
    }

    /**
     * Batch load into tmp_import (chunks of 10k)
     * @param array $rows [ [id, prefix_b, price, date_from, date_to], ... ]
     */
    private function loadIntoTemp(array $rows): void
    {
        if (empty($rows)) return;

        $chunkSize = 10000;
        $chunks = array_chunk($rows, $chunkSize);
        foreach ($chunks as $chunk) {
            Yii::$app->db->createCommand()->batchInsert(
                'tmp_import',
                ['pricelist_filter_b_id', 'prefix_b', 'b_number_price', 'date_from', 'date_to'],
                $chunk
            )->execute();
        }
    }
}
