<?php

namespace app\controllers\json;

use app\models\billing_uu\Major;
use app\models\billing_uu\PricelistFilterB;
use app\models\billing_uu\PricelistPrefixPrice;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\billing_uu\PricelistLocation;
use app\models\billing_uu\PricelistPrefixPriceHistory;
use app\models\billing_uu\PricelistPrefixPriceHistoryItem;
use DateTime;
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
                    ['b.*', 'date_trunc(\'second\', time_start) as time_start',
                    'date_trunc(\'second\', time_end) as time_end',
                    'filter_country' => 'm.country_code']
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
                $upsertResult = $this->upsertPrefixesSql($item);
                if (isset($upsertResult['error'])) {
                    return $upsertResult;
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
    
    private function validateInput()
    {
        if (preg_match("/[^\d,.\-\s]/", $this->request['prefixes'])) {
            return ['error' => 'Некорректный формат префиксов! Допустимы только цифры, тире, точка, запятая, пробел и табуляция.', 'field' => 'prefixes'];
        }
    }
    
    private function prepareDates($rawDateStart, $rawDateEnd)
    {
        if (isset($rawDateStart)) {
            $dt = DateTime::createFromFormat("d.m.Y", $rawDateStart);
            if ($dt !== false && !array_sum($dt::getLastErrors())) {
                if ($dt < DateTime::createFromFormat("Y-m-d", date('Y-m-d'))) {
                    return ['error' => 'Дата начала действия не может быть раньше, чем сейчас!', 'field' => 'prefixes'];
                }
                $dateStart = $dt->format('Y-m-d');
            } else {
                return ['error' => 'Некорректный формат даты начала действия!', 'field' => 'prefixes'];
            }
        } else {
            $dateStart = date('Y-m-d');
        }
        
        if (isset($rawDateEnd) && $rawDateEnd !== '') {
            $dtEnd = DateTime::createFromFormat("d.m.Y", $rawDateEnd);
            if ($dtEnd !== false && !array_sum($dtEnd::getLastErrors())) {
                if ($dtEnd < DateTime::createFromFormat("Y-m-d", $dateStart)) {
                    return ['error' => 'Дата окончания действия не может быть раньше, чем дата начала действия!', 'field' => 'prefixes'];
                }
                $dateEnd = $dtEnd->format('Y-m-d');
            } else {
                return ['error' => 'Некорректный формат даты окончания действия!', 'field' => 'prefixes'];
            }
        } else {
            $dateEnd = '3000-01-01';
        }
        
        return ['date_start' => $dateStart, 'date_end' => $dateEnd];
    }
    
    private function upsertPrefixesSql($item)
    {
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
            
            $historyObject = PricelistPrefixPriceHistory::createHistory($item->id, $pricelistId['pricelist_id'], '3000-01-01', '3000-01-01',
                0, (isset($this->request['prefixes_replace']) && $this->request['prefixes_replace']) ? 'replace' : 'add');
            
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
        
        \Yii::$app->db->createCommand("alter table billing_uu.pricelist_prefix_price disable trigger notify")->queryAll();
        
        $historyObject->total_count = count($prefixesToSave);
        $historyObject->date_from = $dateStart;
        $historyObject->date_to = $dateEnd;
        
        $historyObject->save();
        
        $historyItems = [];
        
        foreach ($prefixesToSave as $prefixToSave) {
            $historyItems[] = PricelistPrefixPrice::updateOldWithHistory($prefixToSave, $historyObject->id);
        }
        
        \Yii::$app->db->createCommand()->batchInsert(
            'billing_uu.pricelist_prefix_price',
            ['pricelist_filter_b_id', 'prefix_b', 'b_number_price', 'date_from', 'date_to', 'history_id'],
            $prefixesToSave
        )->execute();
        
        if (isset($this->request['prefixes_replace']) && $this->request['prefixes_replace']) {
            $dataRemoved = PricelistPrefixPrice::find()
                ->where(['pricelist_filter_b_id' => $item->id])
                ->andWhere('date_to > now()')
                ->andWhere('history_id is null OR history_id <> :historyId')
                ->addParams([':historyId' => $historyObject->id])
                ->all();
            
            foreach ($dataRemoved as $removedItem) {
                $historyItems[] = [
                    $historyObject->id,
                    $removedItem->prefix_b,
                    $removedItem->b_number_price,
                    '',
                    $removedItem->date_from,
                    date('Y-m-d'),
                    'delete'
                ];
                
                $removedItem->date_to = date('Y-m-d');
                $removedItem->save();
            }
        }
        
        \Yii::$app->db->createCommand()->batchInsert(
            'billing_uu.pricelist_prefix_price_history_item',
            ['pricelist_prefix_price_history_id', 'prefix_b', 'price_old', 'price_new', 'date_from', 'date_to', 'type'],
            $historyItems
        )->execute();
        
        \Yii::$app->db->createCommand("alter table billing_uu.pricelist_prefix_price enable trigger notify")->queryAll();
            
        \Yii::$app->db->createCommand("select event.notify('nnp_pricelist_prefix_price', 0);")->queryAll();
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
        
        $historyObject = PricelistPrefixPriceHistory::createHistory($item->id, $pricelistId['pricelist_id'], $dateStart, $dateEnd,
            count($prefixesToSave), (isset($this->request['prefixes_replace']) && $this->request['prefixes_replace']) ? 'replace' : 'add');

        \Yii::$app->db->createCommand("alter table billing_uu.pricelist_prefix_price disable trigger notify")->queryAll();
            
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
        
        \Yii::$app->db->createCommand("alter table billing_uu.pricelist_prefix_price enable trigger notify")->queryAll();
            
        \Yii::$app->db->createCommand("select event.notify('nnp_pricelist_prefix_price', 0);")->queryAll();
    }
    
    /**
     * @throws StaleObjectException
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
    }
}
