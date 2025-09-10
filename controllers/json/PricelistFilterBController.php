<?php

namespace app\controllers\json;

use app\models\billing_uu\Major;
use app\models\billing_uu\PricelistFilterB;
use app\models\billing_uu\PricelistPrefixPrice;
use Yii;
use app\classes\JsonController;
use app\controllers\ImporterController;
use app\exceptions\FormValidationException;
use app\models\billing_uu\PricelistLocation;
use app\models\billing_uu\PricelistPrefixPriceHistory;
use app\models\billing_uu\PricelistPrefixPriceHistoryItem;
use DateTime;
use Exception;
use yii\db\Expression;
use yii\db\Query;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class PricelistFilterBController extends JsonController
{
    public function actionGet()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        $result =
            PricelistFilterB::find()
                ->alias('b')
                ->select(
                    [
                        'b.*',
                        'date_trunc(\'second\', time_start) as time_start',
                        'date_trunc(\'second\', time_end) as time_end',
                        'filter_country' => 'm.country_code'
                    ]
                )
                ->leftJoin(['m' => Major::tableName()], 'm.id = b.nnp_filter')
                ->with('prefixPriceHistory')
                ->where(['b.id' => $this->request['id']])
                ->asArray()
                ->one();

        return $result;
    }

    public function actionSave()
    {
        set_time_limit(0);
        if (!\Yii::$app->user->can('pricelist_edit') && !\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        $result = [];
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('pricelist_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            $item = $this->getPricelistFilterBOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('pricelist_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            $item = PricelistFilterB::create();
            $result['log'] = ['data_before' => []];
        }
        if (isset($this->request['prefixes'])) {
            $validationResult = $this->validateInput();
            if (isset($validationResult['error'])) {
                return $validationResult;
            }
        }
        $item->load($this->request, '');
        $transaction = PricelistFilterB::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }
            if (isset($this->request['prefixes'])) {
                $upsertResult = $this->processPrefixesArray($item);
                if (isset($upsertResult['error'])) {
                    return $upsertResult;
                } else {
                    $result['result']['import_key'] = $upsertResult['key'];
                    $result['result']['pricelist_filter_b_id'] = $upsertResult['pricelist_filter_b_id'];
                    $result['result']['is_replace'] = $upsertResult['is_replace'];
                }
            }
            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
        $result['log']['data_after'] = $this->getDataForLog($item);

        return $result;
    }

    public function actionSaveAndUpdate()
    {
        if (!\Yii::$app->user->can('pricelist_edit') && !\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $result = [];

        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('pricelist_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }

            $item = $this->getPricelistFilterBOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('pricelist_create')) {
                throw new ForbiddenHttpException('Access denied');
            }

            $item = PricelistFilterB::create();
            $result['log'] = ['data_before' => []];
        }

        if (isset($this->request['prefixes'])) {
            $validationResult = $this->validateInput();
            if (isset($validationResult['error'])) {
                return $validationResult;
            }
        }

        $item->load($this->request, '');

        $transaction = PricelistFilterB::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }

            if (isset($this->request['prefixes'])) {
                $upsertResult = $this->upsertPrefixes($item);
                if (isset($upsertResult['error'])) {
                    return $upsertResult;
                }
            }

            $id = $item->id;

            (new Query())->select(new Expression('billing_uu.copy_b_nnp_filter(:filter_b_id)'))
                ->addParams([
                    ':filter_b_id' => $id
                ])->one();

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }

        if ($id) {
            $item = $this->getPricelistFilterBOr404($id);
        }

        $result['log']['data_after'] = $this->getDataForLog($item);

        return $result;
    }

    public function actionDeleteHistoryItem()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Доступ запрещен');
        }
        $id = \Yii::$app->request->post('id');
        if (!$id) {
            throw new \yii\web\BadRequestHttpException('Требуется указать ID');
        }
        $item = PricelistPrefixPriceHistory::findOne($id);
        if (!$item) {
            throw new \yii\web\NotFoundHttpException("Элемент с ID $id не найден");
        }
        if ($item->delete() !== false) {
            return ['status' => 'success', 'message' => "Элемент с ID $id успешно удален"];
        } else {
            throw new \yii\web\ServerErrorHttpException('Не удалось удалить элемент');
        }
    }

    private function validateInput()
    {
        if (preg_match("/[^\d,.\-\s]/", $this->request['prefixes'])) {
            return ['error' => 'Некорректный формат префиксов! Допустимы только цифры, тире, точка, запятая, пробел и табуляция.', 'field' => 'prefixes'];
        }
    }

    /**
     * Поддержка дат в форматах d.m.Y и Y-m-d. Возвращает Y-m-d.
     */
    private function prepareDates($rawDateStart, $rawDateEnd)
    {
        // ---- date_from
        if (isset($rawDateStart)) {
            $dateStart = null;

            // сначала пробуем d.m.Y
            $dt = DateTime::createFromFormat("d.m.Y", $rawDateStart);
            if ($dt !== false && !array_sum($dt::getLastErrors())) {
                $dateStart = $dt->format('Y-m-d');
            } else {
                // затем Y-m-d
                $dt2 = DateTime::createFromFormat("Y-m-d", $rawDateStart);
                if ($dt2 !== false && !array_sum($dt2::getLastErrors())) {
                    $dateStart = $dt2->format('Y-m-d');
                }
            }

            if (!$dateStart) {
                return ['error' => 'Некорректный формат даты начала действия!', 'field' => 'prefixes'];
            }

            if (DateTime::createFromFormat("Y-m-d", $dateStart) < DateTime::createFromFormat("Y-m-d", date('Y-m-d'))) {
                return ['error' => 'Дата начала действия не может быть раньше, чем сейчас!', 'field' => 'prefixes'];
            }
        } else {
            $dateStart = date('Y-m-d');
        }

        // ---- date_to
        if (isset($rawDateEnd) && $rawDateEnd !== '') {
            $dateEnd = null;

            $dtEnd = DateTime::createFromFormat("d.m.Y", $rawDateEnd);
            if ($dtEnd !== false && !array_sum($dtEnd::getLastErrors())) {
                $dateEnd = $dtEnd->format('Y-m-d');
            } else {
                $dtEnd2 = DateTime::createFromFormat("Y-m-d", $rawDateEnd);
                if ($dtEnd2 !== false && !array_sum($dtEnd2::getLastErrors())) {
                    $dateEnd = $dtEnd2->format('Y-m-d');
                }
            }

            if (!$dateEnd) {
                return ['error' => 'Некорректный формат даты окончания действия!', 'field' => 'prefixes'];
            }

            if (DateTime::createFromFormat("Y-m-d", $dateEnd) < DateTime::createFromFormat("Y-m-d", $dateStart)) {
                return ['error' => 'Дата окончания действия не может быть раньше, чем дата начала действия!', 'field' => 'prefixes'];
            }
        } else {
            $dateEnd = '3000-01-01';
        }

        return ['date_start' => $dateStart, 'date_end' => $dateEnd];
    }

    private function processPrefixesArray($item)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        $prefixesToSave = [];
        $prefixesArray = explode("\n", $this->request['prefixes']);
        try {
            $pricelistId = PricelistLocation::find()
                ->alias('pl')
                ->select(['pl.pricelist_id'])
                ->innerJoin('billing_uu.pricelist_filter_a a', 'a.pricelist_location_id = pl.id')
                ->where(['a.id' => $item->pricelist_filter_a_id])
                ->asArray()
                ->one();
            $interconnectPrice = floatval($item->interconnect_price);
            $historyObjectList = [];
            $repeatingPrefixes = [];

            foreach ($prefixesArray as $prefixItem) {
                $input = preg_split("/[\t]/", $prefixItem);
                $prefixBString = $input[0];
                $prefixPrice = $input[1];
                $rawDateStart = $input[2]; // Если даты нет, то ругнется, и правильно сделает. Для этого внизу catch().
                $rawDateEnd = isset($input[3]) ? $input[3] : '01.01.3000';

                $preparedDates = $this->prepareDates($rawDateStart, $rawDateEnd);
                if (isset($preparedDates['error'])) {
                    return $preparedDates;
                } else {
                    $dateStart = $preparedDates['date_start'];
                    $dateEnd = $preparedDates['date_end'];
                }
                if (isset($historyObjectList[$dateStart])) {
                    $historyObject = $historyObjectList[$dateStart];
                } else {
                    $historyObject = PricelistPrefixPriceHistory::createHistory(
                        $item->id,
                        $pricelistId['pricelist_id'],
                        $dateStart,
                        '3000-01-01',
                        0,
                        (isset($this->request['prefixes_replace']) && $this->request['prefixes_replace']) ? 'replace' : 'add',
                        false
                    );
                    $historyObjectList[$dateStart] = $historyObject;
                }
                $prefixBArray = explode(',', str_replace(['-'], ',', $prefixBString));
                foreach ($prefixBArray as $prefixB) {
                    if (isset($repeatingPrefixes[$prefixB])) {
                        throw new Exception('Повторяющийся префикс! ' . $prefixB);
                    }
                    $repeatingPrefixes[$prefixB] = $prefixB;

                    $bNumberPrice = str_replace(',', '.', $prefixPrice);
                    $bNumberPrice = floatval($bNumberPrice);
                    $prefixesToSave[$dateStart][] = [
                        $item->id,
                        trim($prefixB),
                        (string)($bNumberPrice - $interconnectPrice),
                        $dateStart,
                        $dateEnd,
                        $historyObject->id
                    ];
                }
            }

        } catch (\Exception $e) {
            return ['error' => 'Ошибка при обработке префиксов! Каждая пара префикс-цена должна быть на отдельной строке. Префиксы должны быть отделены от цены символом табуляции. Префиксы можно перечислять через запятую или через тире.', 'field' => 'prefixes'];
        }
        $key = md5(microtime(true));
        Yii::$app->cache->set($key, $prefixesToSave, ImporterController::CACHE_TIMEOUT);
        Yii::$app->cache->set($key . '_history', $historyObjectList, ImporterController::CACHE_TIMEOUT);
        return [
            'key' => $key,
            'pricelist_filter_b_id' => $item->id,
            'is_replace' => (isset($this->request['prefixes_replace']) && $this->request['prefixes_replace'])
        ];
    }

    private function upsertPrefixes($item)
    {
        $prefixesToSave = [];
        $prefixesArray = explode("\n", $this->request['prefixes']);
        try {
            $interconnectPrice = floatval($item->interconnect_price);
            foreach ($prefixesArray as $prefixItem) {
                $input = preg_split("/[\t]/", $prefixItem);
                $prefixBString = $input[0];
                $prefixPrice = $input[1];
                $rawDateStart = $input[2]; // Если даты нет, то ругнется, и правильно сделает. Для этого внизу catch().
                $rawDateEnd = isset($input[3]) ? $input[3] : '01.01.3000';
                $preparedDates = $this->prepareDates($rawDateStart, $rawDateEnd);
                if (isset($preparedDates['error'])) {
                    return $preparedDates;
                } else {
                    $dateStart = $preparedDates['date_start'];
                    $dateEnd = $preparedDates['date_end'];
                }
                $prefixBArray = explode(',', str_replace(['-'], ',', $prefixBString));
                foreach ($prefixBArray as $prefixB) {
                    $bNumberPrice = str_replace(',', '.', $prefixPrice);
                    $bNumberPrice = floatval($bNumberPrice);
                    $prefixesToSave[] = [
                        'pricelist_filter_b_id' => $item->id,
                        'prefix_b' => trim($prefixB),
                        'b_number_price' => (string)($bNumberPrice - $interconnectPrice),
                        'date_from' => $dateStart,
                        'date_to' => $dateEnd
                    ];
                }
            }
        } catch (\Exception $e) {
            return ['error' => 'Ошибка при обработке префиксов! Каждая пара префикс-цена должна быть на отдельной строке. Префиксы должны быть отделены от цены символом табуляции. Префиксы можно перечислять через запятую или через тире.', 'field' => 'prefixes'];
        }
        $pricelistId = PricelistLocation::find()
            ->alias('pl')
            ->select(['pl.pricelist_id'])
            ->innerJoin('billing_uu.pricelist_filter_a a', 'a.pricelist_location_id = pl.id')
            ->where(['a.id' => $item->pricelist_filter_a_id])
            ->asArray()
            ->one();
        $historyObject = PricelistPrefixPriceHistory::createHistory(
            $item->id,
            $pricelistId['pricelist_id'],
            $dateStart,
            $dateEnd,
            count($prefixesToSave),
            (isset($this->request['prefixes_replace']) && $this->request['prefixes_replace']) ? 'replace' : 'add'
        );

        foreach ($prefixesToSave as $prefixToSave) {
            $prefixCreatedItem = PricelistPrefixPrice::create($prefixToSave, $historyObject->id);
            if (!$prefixCreatedItem->save()) {
                throw new FormValidationException($item);
            }
        }
        if (isset($this->request['prefixes_replace']) && $this->request['prefixes_replace']) {
            $dataRemoved = PricelistPrefixPrice::find()
                ->where(['pricelist_filter_b_id' => $item->id])
                ->andWhere('date_to > now()')
                ->andWhere('history_id <> :historyId')
                ->addParams([':historyId' => $historyObject->id])
                ->all();
            foreach ($dataRemoved as $removedItem) {
                $historyItemData = [
                    'pricelist_prefix_price_history_id' => $historyObject->id,
                    'prefix_b' => $removedItem->prefix_b,
                    'price_old' => $removedItem->b_number_price,
                    'price_new' => '',
                    'date_from' => $removedItem->date_from,
                    'date_to' => date('Y-m-d'),
                    'type' => 'delete'
                ];
                $historyItemObject = PricelistPrefixPriceHistoryItem::create($historyItemData);
                $historyItemObject->save();
                $removedItem->date_to = date('Y-m-d');
                $removedItem->save();
            }
        }
    }

    /**
     * Массовый импорт фильтров B и прайсов префикса ('') по формату:
     * country_code  operator_code  price  date_from  date_to
     */
   /**
 * Массовый импорт фильтров B и прайсов префикса ('') по формату:
 * mcc  operator_code  price  date_from  date_to
 *
 * ВАЖНО: первая колонка — ТОЛЬКО MCC. Резолвим MCC → внутренний nnp.country.code.
 * Если MCC не найден — строка идёт в ошибки.
 */
public function actionBulkImport()
{
    if (!\Yii::$app->user->can('pricelist_edit') && !\Yii::$app->user->can('pricelist_create')) {
        throw new ForbiddenHttpException('Access denied');
    }

    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $body      = $this->request;
    $aId       = (int)($body['pricelist_filter_a_id'] ?? 0);
    $template  = $body['template'] ?? [];
    $rows      = trim((string)($body['rows'] ?? ''));
    $delimiter = $body['delimiter'] ?? 'auto';
    $replace   = !empty($body['replace']);
    $dryRun    = !empty($body['dry_run']);

    if (!$aId || $rows === '') {
        return ['ok' => false, 'errors' => [['line' => 0, 'message' => 'pricelist_filter_a_id и rows обязательны']]];
    }

    // --- Предзагрузка соответствий MCC → внутренний code ---
    $countryRows = (new Query())
        ->select(['code', 'mcc'])
        ->from('nnp.country')
        ->all();

    $countryByMcc = []; // '412' => 4
    foreach ($countryRows as $cr) {
        $code = isset($cr['code']) ? (int)$cr['code'] : null;
        if ($code === null) continue;
        $mcc = isset($cr['mcc']) ? trim((string)$cr['mcc']) : '';
        if ($mcc !== '') {
            $countryByMcc[$mcc] = $code;
        }
    }
    // ------------------------------------------------------

    // Парсинг строк
    $parsed = $this->bulkParseRows($rows, $delimiter);
    if (!$parsed['ok']) return $parsed;

    $preview  = [];
    $errors   = [];
    $normRows = [];
    $lineNo   = 0;

    foreach ($parsed['rows'] as $r) {
        $lineNo++;

        if (count($r) < 5) {
            $errors[] = ['line' => $lineNo, 'message' => 'Ожидалось 5 полей: country(operator MCC) operator price date_from date_to'];
            continue;
        }

        list($mccRaw, $operatorCode, $priceRaw, $dfRaw, $dtRaw) = $r;
        $mccRaw       = trim((string)$mccRaw);
        $operatorCode = trim((string)$operatorCode);

        // --- Резолв MCC → внутренний code (без фолбэка на code) ---
        $resolvedCountryCode = ($mccRaw !== '' && isset($countryByMcc[$mccRaw])) ? $countryByMcc[$mccRaw] : null;
        if ($resolvedCountryCode === null) {
            $errors[] = [
                'line'    => $lineNo,
                'message' => "Страна не найдена по MCC '{$mccRaw}'"
            ];
            continue;
        }
        // -----------------------------------------------------------

        // цена
        $priceStr = str_replace(',', '.', trim((string)$priceRaw));
        if (!is_numeric($priceStr)) {
            $errors[] = ['line' => $lineNo, 'message' => 'Некорректная цена'];
            continue;
        }
        $price = (float)$priceStr;

        // даты: допускаем DD.MM.YYYY и YYYY-MM-DD
        $prepared = $this->prepareDates($dfRaw, $dtRaw);
        if (isset($prepared['error'])) {
            $errors[] = ['line' => $lineNo, 'message' => $prepared['error']];
            continue;
        }
        $dateFrom = $prepared['date_start'];
        $dateTo   = $prepared['date_end'];

        // Нормализованная строка — теперь country_code всегда ВНУТРЕННИЙ code (из nnp.country.code)
        $normRows[] = [
            'line'          => $lineNo,
            'country_code'  => (string)$resolvedCountryCode,
            'operator_code' => $operatorCode,
            'price'         => $price,
            'date_from'     => $dateFrom,
            'date_to'       => $dateTo
        ];

        if (count($preview) < 50) {
            $preview[] = [
                'country_code'  => (string)$resolvedCountryCode,
                'operator_code' => $operatorCode,
                'price'         => $price,
                'date_from'     => $dateFrom,
                'date_to'       => $dateTo,
                'note'          => ''
            ];
        }
    }

    if ($dryRun) {
        $summary = "Готово к обработке " . count($normRows) . " строк" . ($replace ? " (режим полной замены фильтров B)" : "");
        return ['ok' => empty($errors), 'dry_run' => true, 'preview' => $preview, 'errors' => $errors, 'summary' => $summary];
    }

    if (!empty($errors)) {
        return ['ok' => false, 'dry_run' => false, 'preview' => $preview, 'errors' => $errors, 'summary' => 'Исправьте ошибки и повторите'];
    }

    $tx = PricelistFilterB::getDb()->beginTransaction();
    try {
        if ($replace) {
            Yii::$app->db->createCommand("
                DELETE FROM billing_uu.pricelist_filter_b WHERE pricelist_filter_a_id = :aId
            ")->bindValue(':aId', $aId)->execute();
        }

        $created = 0; $updated = 0; $pricesUpserted = 0;

        // получаем pricelist_id для истории прайсов
        $pl = PricelistLocation::find()
            ->alias('pl')
            ->select(['pl.pricelist_id'])
            ->innerJoin('billing_uu.pricelist_filter_a a', 'a.pricelist_location_id = pl.id')
            ->where(['a.id' => $aId])
            ->asArray()
            ->one();

        if (!$pl) {
            throw new \RuntimeException('Не найден прайс-лист для фильтра A');
        }

        $pricelistId = (int)$pl['pricelist_id'];

        foreach ($normRows as $row) {
            // Найти/создать/обновить фильтр B под пару (country, operator)
            $res = $this->findOrCreateFilterBForPair($aId, $row['country_code'], $row['operator_code'], $template);
            if ($res['action'] === 'create') $created++; else $updated++;

            /** @var PricelistFilterB $b */
            $b = $res['model'];

            // Upsert прайса префикса для пустого prefix_b на заданный интервал и цену
            $pricesUpserted += $this->upsertBlankPrefixPrice($b, $row['price'], $row['date_from'], $row['date_to'], $pricelistId);
        }

        $tx->commit();

        $summary = "Фильтры B: создано $created, обновлено $updated. Прайсов префикса (''): $pricesUpserted.";
        return ['ok' => true, 'dry_run' => false, 'summary' => $summary];

    } catch (\Throwable $e) {
        if ($tx->getIsActive()) $tx->rollBack();
        return ['ok' => false, 'dry_run' => false, 'errors' => [['line' => 0, 'message' => $e->getMessage()]], 'summary' => 'Ошибка транзакции'];
    }
}

    /**
     * Удаление фильтра B
     * @throws HttpException
     * @throws \Exception
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('pricelist_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        $item = $this->getPricelistFilterBOr404($this->request['id']);
        $item->delete();
        return ['success' => 1];
    }

    // =========================
    // ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ
    // =========================

    /**
     * Разбор многострочного ввода в массив полей.
     */
    private function bulkParseRows(string $rows, string $delimiter = 'auto'): array
    {
        $out = [];
        $lines = preg_split('/\R/u', $rows, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') continue;

            if ($delimiter === 'auto') {
                if (strpos($line, "\t") !== false) {
                    $parts = preg_split("/\t/u", $line);
                } elseif (strpos($line, ';') !== false) {
                    $parts = preg_split("/\s*;\s*/u", $line);
                } elseif (strpos($line, ',') !== false) {
                    $parts = preg_split("/\s*,\s*/u", $line);
                } else {
                    $parts = preg_split("/\s{2,}/u", $line);
                }
            } else {
                $map = [
                    'tab'   => "/\t/u",
                    'semi'  => "/\s*;\s*/u",
                    'comma' => "/\s*,\s*/u",
                    'space' => "/\s{2,}/u",
                ];
                $re = $map[$delimiter] ?? "/\t/u";
                $parts = preg_split($re, $line);
            }

            $out[] = array_map('trim', $parts);
        }
        return ['ok' => true, 'rows' => $out];
    }

    /**
     * Найти существующий B по (country, operator) под A; если нет — создать.
     * Шаблонные поля переписываем (обновление).
     */
    private function findOrCreateFilterBForPair(int $aId, $countryCode, $operatorCode, array $template): array
    {
        $arrCountry  = '{' . (int)$countryCode . '}';
        $arrOperator = '{' . (int)$operatorCode . '}';

        $sql = <<<SQL
SELECT id FROM billing_uu.pricelist_filter_b
WHERE pricelist_filter_a_id = :aId
  AND nnp_country = :c::int[]
  AND COALESCE(f_inv_nnp_country,false) = false
  AND nnp_operator = :o::int[]
  AND COALESCE(f_inv_nnp_operator,false) = false
  AND (nnp_region IS NULL OR nnp_region = '{}')
  AND (nnp_city   IS NULL OR nnp_city   = '{}')
  AND (nnp_ndc    IS NULL OR nnp_ndc    = '{}')
  AND (nnp_ndc_type IS NULL OR nnp_ndc_type = '{}')
LIMIT 1
SQL;

        $row = Yii::$app->db->createCommand($sql, [':aId' => $aId, ':c' => $arrCountry, ':o' => $arrOperator])->queryOne();

        if ($row && isset($row['id'])) {
            $b = PricelistFilterB::findOne($row['id']);
            $this->applyTemplateToB($b, $template);
            $b->nnp_country = $arrCountry;
            $b->nnp_operator = $arrOperator;
            $b->f_inv_nnp_country = false;
            $b->f_inv_nnp_operator = false;
            if (!$b->save()) throw new FormValidationException($b);
            return ['action' => 'update', 'model' => $b];
        } else {
            $b = PricelistFilterB::create();
            $b->pricelist_filter_a_id = $aId;
            $this->applyTemplateToB($b, $template);
            $b->nnp_country = $arrCountry;
            $b->nnp_operator = $arrOperator;
            $b->f_inv_nnp_country = false;
            $b->f_inv_nnp_operator = false;
            if (!$b->save()) throw new FormValidationException($b);
            return ['action' => 'create', 'model' => $b];
        }
    }

    /**
     * Применить шаблонные поля формы к модели B (только whitelisted-поля)
     */
    private function applyTemplateToB(PricelistFilterB $b, array $tpl): void
    {
        $fields = [
            'mode_selected',
            'interconnect_price',
            'ported_num_price',
            'operator_price',
            'transit_price',
            'tarification_free_seconds',
            'tarification_interval_seconds',
            'tarification_type',
            'tarification_min_paid_seconds',
            'time_start',
            'time_end',
            'rating',
            'use_for_minimum',
            'use_cutoff_for_minimum',
            'consider_porting_mode',
            'description',
            'nnp_filter'
        ];
        foreach ($fields as $f) {
            if (array_key_exists($f, $tpl)) $b->$f = $tpl[$f];
        }
    }

    /**
     * Upsert записи прайса префикса для пустого prefix_b ('') на интервал дат.
     * Возвращает 1, если создано/обновлено.
     * Семантика цены: сохраняем (input_price - interconnect_price).
     */
    private function upsertBlankPrefixPrice(PricelistFilterB $b, float $inputPrice, string $dateFrom, string $dateTo, int $pricelistId): int
    {
        $diffPrice = (string)($inputPrice - floatval($b->interconnect_price));

        // ищем запись с таким date_from
        $exists = PricelistPrefixPrice::find()
            ->where(['pricelist_filter_b_id' => $b->id, 'prefix_b' => '', 'date_from' => $dateFrom])
            ->andWhere('date_to > now()')
            ->one();

        // история
        $history = PricelistPrefixPriceHistory::createHistory(
            $b->id, $pricelistId, $dateFrom, $dateTo, 1, 'add'
        );

        if (!$exists) {
            $data = [
                'prefix_b' => '',
                'pricelist_filter_b_id' => $b->id,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'b_number_price' => $diffPrice
            ];
            $item = PricelistPrefixPrice::create($data, $history->id);
            if (!$item->save()) throw new FormValidationException($item);
            return 1;
        } else {
            $old = $exists->b_number_price;
            $exists->b_number_price = $diffPrice;
            $exists->date_to = $dateTo;

            $histItem = [
                'pricelist_prefix_price_history_id' => $history->id,
                'prefix_b' => '',
                'price_old' => $old,
                'price_new' => $diffPrice,
                'date_from' => $dateFrom,
                'date_to' => $dateTo
            ];
            if ($old < $diffPrice) $histItem['type'] = 'increase';
            elseif ($old > $diffPrice) $histItem['type'] = 'decrease';
            elseif ($dateTo === '3000-01-01') $histItem['type'] = 'prolong';
            else $histItem['type'] = 'delete';

            $histObj = PricelistPrefixPriceHistoryItem::create($histItem);
            $histObj->save();

            if (!$exists->save()) throw new FormValidationException($exists);
            return 1;
        }
    }

    // Внутри класса PricelistFilterBController, рядом с другими private-методами:

/**
 * Разрешает входной "код страны" в внутренний code из nnp.country:
 *  - сначала пытается как MCC (nnp.country.mcc),
 *  - затем как прямой code (nnp.country.code).
 * Возвращает int code или null, если не найдено.
 */
private function resolveCountryCode($input)
{
    if ($input === null || $input === '') {
        return null;
    }
    // нормализуем строковое/числовое
    $val = trim((string)$input);

    // 1) Поиск по MCC
    $row = (new Query())
        ->select(['code'])
        ->from('nnp.country')
        ->where(['mcc' => $val])
        ->limit(1)
        ->one();
    if ($row && isset($row['code'])) {
        return (int)$row['code'];
    }

    // 2) Фолбэк: попробовать как прямой code
    $row2 = (new Query())
        ->select(['code'])
        ->from('nnp.country')
        ->where(['code' => (int)$val])
        ->limit(1)
        ->one();
    if ($row2 && isset($row2['code'])) {
        return (int)$row2['code'];
    }

    return null;
}

}