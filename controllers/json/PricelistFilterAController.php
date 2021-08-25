<?php

namespace app\controllers\json;

use app\classes\BaseController;
use app\models\billing_uu\Major;
use app\models\billing_uu\PricelistFilterA;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\billing_uu\Pricelist;
use app\models\billing_uu\PricelistFilterB;
use app\models\billing_uu\PricelistFilterBHistory;
use app\models\billing_uu\PricelistFilterBHistoryItem;
use app\models\billing_uu\A2pAlphaNum;
use app\models\billing_uu\A2pAlphaNumHistory;
use app\models\billing_uu\A2pAlphaNumHistoryItem;
use app\models\billing_uu\PricelistGroup;
use app\models\billing_uu\PricelistLocation;
use app\models\billing_uu\PricelistPrefixPrice;
use app\models\billing_uu\PricelistPrefixPriceHistory;
use app\models\billing_uu\PricelistPrefixPriceHistoryItem;
use DateTime;
use yii\db\Expression;
use yii\db\Query;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class PricelistFilterAController extends JsonController
{

    public function actionGet()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            PricelistFilterA::find()
                ->alias('a')
                ->select(
                    ['a.*', 'date_trunc(\'second\', time_start) as time_start',
                    'date_trunc(\'second\', time_end) as time_end',
                    'filter_country' => 'm.country_code',
                    'pricelist_group_id' => 'g.id',
                    'pricelist_group_name' => 'g.name']
                )
                ->with('filterBHistory')
                ->with('alphaNumHistory')
                ->leftJoin(['m' => Major::tableName()], 'm.id = a.nnp_filter')
                ->innerJoin([ 'l' => PricelistLocation::tableName()], 'l.id = a.pricelist_location_id')
                ->innerJoin(['p' => Pricelist::tableName()], 'p.id = l.pricelist_id')
                ->innerJoin(['g' => PricelistGroup::tableName()], 'g.id = p.pricelist_group_id')
                ->where(['a.id' => $this->request['id']])
                ->asArray()
                ->one();
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
            
            $item = $this->getPricelistFilterAOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('pricelist_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = PricelistFilterA::create();
            $result['log'] = ['data_before' => []];
        }
        
        $item->load($this->request, '');

        $transaction = PricelistFilterA::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }
            if (isset($this->request['filters'])) {
                $upsertResult = $this->upsertFilters($item);
                if (isset($upsertResult['error'])) {
                    return $upsertResult;
                }
            }

            if (isset($this->request['alphanums'])) {
                $upsertAlphaNums = $this->upsertAlphaNums($item);
                if (isset($upsertAlphaNums['error'])) {
                    return $upsertAlphaNums;
                } else {
                    $result['result']['import_key'] = $upsertAlphaNums['key'];
                    $result['result']['pricelist_filter_a_id'] = $upsertAlphaNums['pricelist_filter_a_id'];
                    $result['result']['is_replace'] = $upsertAlphaNums['is_replace'];
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

            $item = $this->getPricelistFilterAOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('pricelist_create')) {
                throw new ForbiddenHttpException('Access denied');
            }

            $item = PricelistFilterA::create();
            $result['log'] = ['data_before' => []];
        }

        $item->load($this->request, '');

        $transaction = PricelistFilterA::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }

            $id = $item->id;

            (new Query())->select(new Expression('billing_uu.copy_a_nnp_filter(:filter_a_id)'))
                ->addParams([
                    ':filter_a_id' => $item->id
                ])->one();

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }

        if ($id) {
            $item = $this->getPricelistFilterAOr404($id);
        }

        $result['log']['data_after'] = $this->getDataForLog($item);

        return $result;
    }
    
    private function upsertFilters($item)
    {
        $pricelistData = Pricelist::find()
            ->alias('p')
            ->select(['p.id', 'p.type_id', 'p.default_tarification_free_seconds', 'p.default_tarification_interval_seconds', 'p.default_tarification_min_paid_seconds'])
            ->innerJoin('billing_uu.pricelist_location pl', 'pl.pricelist_id = p.id')
            ->where(['pl.id' => $item->pricelist_location_id])
            ->asArray()
            ->one();
        
        $filtersArray = explode("\n", $this->request['filters']);
        try {
            $nnpFilterIds = [];
            $inputArray = [];
            
            $filterBHistoryObject = PricelistFilterBHistory::createHistory($item->id, $pricelistData['id'], '3000-01-01', '3000-01-01', count($filtersArray), 'add');
            
            foreach ($filtersArray as $filterItem) {
                $input = preg_split("/[\t]/", $filterItem);
                
                if (in_array($input[0], $nnpFilterIds)) {
                    throw new BadRequestHttpException('Одинаковые id nnp-фильтров!', 500);
                }
                
                $nnpFilterIds[] = $input[0];
                $inputArray[] = $input;
            }
            
            foreach ($inputArray as $input) {
                $nnpFilterId = $input[0];
                $filterBDescription = $input[1];
                $prefixPrice = $input[2];
                $rawDateStart = $input[3]; // Если даты нет, то ругнется, и правильно сделает. Для этого внизу catch().
                $rawDateEnd = isset($input[4]) ? $input[4] : '01.01.3000';
                
                $preparedDates = $this->prepareDates($rawDateStart, $rawDateEnd);
                
                if (isset($preparedDates['error'])) {
                    return $preparedDates;
                } else {
                    $dateStart = $preparedDates['date_start'];
                    $dateEnd = $preparedDates['date_end'];
                }
                
                $filterBObject = PricelistFilterB::find()
                    ->where(['pricelist_filter_a_id' => $item->id, 'nnp_filter' => $nnpFilterId])
                    ->one();
                
                $filterBHistoryItem = [
                    'pricelist_filter_b_history_id' => $filterBHistoryObject->id,
                    'description' => $filterBDescription,
                    'date_from' => $dateStart,
                    'date_to' => $dateEnd,
                    'nnp_filter_id' => $nnpFilterId
                ];
                    
                if (isset($filterBObject)) {
                    $filterBHistoryItem['type'] = 'edit';
                    
                    $filterBObject->description = $filterBDescription;
                    $filterBObject->save();
                    
                    $historyObject = PricelistPrefixPriceHistory::createHistory($filterBObject->id, $pricelistData['id'], $dateStart, $dateEnd, 1, 'add');
                    
                    $prefixPriceObject = PricelistPrefixPrice::find()
                        ->where(['pricelist_filter_b_id' => $filterBObject->id])
                        ->orderBy('id desc')
                        ->one();
                    
                    $bNumberPrice = str_replace(',', '.', $prefixPrice);
                    $bNumberPrice = (string)floatval($bNumberPrice);
                    
                    if (!$prefixPriceObject) {
                        $prefixPriceObject = PricelistPrefixPrice::create([
                            'prefix_b' => '',
                            'pricelist_filter_b_id' => $filterBObject->id,
                            'b_number_price' => $bNumberPrice,
                            'date_from' => $dateStart,
                            'date_to' => $dateEnd
                        ], $historyObject->id);
                        
                        $prefixPriceObject->save();
                    } else {

                        $prefixPriceObject = PricelistPrefixPrice::create([
                            'prefix_b' => '',
                            'pricelist_filter_b_id' => $filterBObject->id,
                            'b_number_price' => $bNumberPrice,
                            'date_from' => $dateStart,
                            'date_to' => $dateEnd
                        ], $historyObject->id);

                        $historyItem = [
                            'pricelist_prefix_price_history_id' => $historyObject->id,
                            'prefix_b' => $prefixPriceObject->prefix_b,
                            'price_old' => $prefixPriceObject->b_number_price,
                            'price_new' => $bNumberPrice,
                            'date_from' => $dateStart,
                            'date_to' => $dateEnd
                        ];
             
                        if ($prefixPriceObject->b_number_price < $bNumberPrice) {
                            $historyItem['type'] = 'increase';
                        } elseif ($prefixPriceObject->b_number_price > $bNumberPrice) {
                            $historyItem['type'] = 'decrease';
                        } elseif ($dateEnd == '3000-01-01') {
                            $historyItem['type'] = 'prolong';
                        } else {
                            $historyItem['type'] = 'delete';
                        }
                        
                        $prefixPriceObject->b_number_price = $bNumberPrice;
                        $prefixPriceObject->date_from = $dateStart;
                        $prefixPriceObject->date_to = $dateEnd;
                        
                        $prefixPriceObject->save();
                    }
                } else {
                    $filterBHistoryItem['type'] = 'add';
                    
                    $tarificationIntervalSeconds = ($pricelistData['type_id'] == 1) ? 60 : 1;
                    $filterBObject = PricelistFilterB::create([
                        'pricelist_filter_a_id' => $item->id,
                        'description' => $filterBDescription,
                        'mode_selected' => true,
                        'interconnect_price' => '0.000000',
                        'ported_num_price' => '0.000000',
                        'operator_price' => '0.000000',
                        'transit_price' => '0.000000',
                        'tarification_free_seconds' => $pricelistData['default_tarification_free_seconds'],
                        'tarification_interval_seconds' => $pricelistData['default_tarification_interval_seconds'],
                        'tarification_type' => 2,
                        'tarification_min_paid_seconds' => $pricelistData['default_tarification_min_paid_seconds'],
                        'rating' => 1,
                        'nnp_filter' => $nnpFilterId
                    ]);
                    
                    $filterBObject->save();
                    
                    $historyObject = PricelistPrefixPriceHistory::createHistory($filterBObject->id, $pricelistData['id'], $dateStart, $dateEnd, 1, 'add');
                    
                    (new Query())->select(new Expression('billing_uu.copy_b_nnp_filter(:filter_b_id)'))
                        ->addParams([
                            ':filter_b_id' => $filterBObject->id
                        ])->one();
                    
                    $bNumberPrice = str_replace(',', '.', $prefixPrice);
                    $bNumberPrice = floatval($bNumberPrice);
                    
                    $prefixPriceObject = PricelistPrefixPrice::create([
                        'prefix_b' => '',
                        'pricelist_filter_b_id' => $filterBObject->id,
                        'b_number_price' => (string)$bNumberPrice,
                        'date_from' => $dateStart,
                        'date_to' => $dateEnd
                    ], $historyObject->id);
                    
                    $prefixPriceObject->save();
                }
                
                $filterBHistoryItemObject = PricelistFilterBHistoryItem::create($filterBHistoryItem);
                $filterBHistoryItemObject->save();
            }
            
            $filterBHistoryObject->date_from = $dateStart;
            $filterBHistoryObject->date_to = $dateEnd;
            
            $filterBHistoryObject->save();
        } catch (BadRequestHttpException $e) {
            return ['error' => $e->getMessage(), 'field' => 'filters'];
        } catch (\Exception $e) {
            return ['error' => 'Ошибка при обработке фильтров!', 'field' => 'filters'];
        }
        
        return true;
    }

    private function upsertAlphaNums($item)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $alphaNumsToSave = [];
        $alphaNumsArray = explode(",", $this->request['alphanums']);

        try {
            $pricelistId = PricelistLocation::find()
                ->alias('pl')
                ->select('pl.pricelist_id')
                ->where(['id' => $item->pricelist_location_id])
                ->asArray()
                ->one();

            $historyObject = A2pAlphanumHistory::createHistory($item->id, $pricelistId['pricelist_id'],
                0, (isset($this->request['alphanum_replace']) && $this->request['alphanum_replace']) ? 'replace' : 'add', false);
            $historyObjectList[] = $historyObject;

            foreach ($alphaNumsArray as $alphaNum) {
                $alphaNumsToSave[] = [
                    $item->id,
                    $alphaNum,
                    $historyObject->id,
                ];

            }

        } catch (\Exception $e) {
            return ['error' => $e->getMessage(), 'field' => 'alphanums'];
        }

        $key = uniqId();
        Yii::$app->cache->set($key, $alphaNumsToSave, BaseController::ONE_DAY);
        Yii::$app->cache->set($key . '_history', $historyObjectList, BaseController::ONE_DAY);
        
        return [
            'key' => $key,
            'pricelist_filter_a_id' => $item->id,
            'is_replace' => (isset($this->request['alphanum_replace']) && $this->request['alphanum_replace'])
        ];
    }
    
    private function prepareDates($rawDateStart, $rawDateEnd)
    {
        if (isset($rawDateStart)) {
            $dt = DateTime::createFromFormat("d.m.Y", $rawDateStart);
            if ($dt !== false && !array_sum($dt::getLastErrors())) {
                if ($dt < DateTime::createFromFormat("Y-m-d", date('Y-m-d'))) {
                    return ['error' => 'Дата начала действия не может быть раньше, чем сейчас!', 'field' => 'filters'];
                }
                $dateStart = $dt->format('Y-m-d');
            } else {
                return ['error' => 'Некорректный формат даты начала действия!', 'field' => 'filters'];
            }
        } else {
            $dateStart = date('Y-m-d');
        }
        
        if (isset($rawDateEnd) && $rawDateEnd !== '') {
            $dtEnd = DateTime::createFromFormat("d.m.Y", $rawDateEnd);
            if ($dtEnd !== false && !array_sum($dtEnd::getLastErrors())) {
                if ($dtEnd < DateTime::createFromFormat("Y-m-d", $dateStart)) {
                    return ['error' => 'Дата окончания действия не может быть раньше, чем дата начала действия!', 'field' => 'filters'];
                }
                $dateEnd = $dtEnd->format('Y-m-d');
            } else {
                return ['error' => 'Некорректный формат даты окончания действия!', 'field' => 'filters'];
            }
        } else {
            $dateEnd = '3000-01-01';
        }
        
        return ['date_start' => $dateStart, 'date_end' => $dateEnd];
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
        
        $item = $this->getPricelistFilterAOr404($this->request['id']);
        $item->delete();
    }
}
