<?php

namespace app\controllers\json;

use app\models\billing_uu\PricelistPrefixPrice;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\billing_uu\PricelistPrefixPriceHistory;
use app\models\billing_uu\PricelistPrefixPriceHistoryItem;
use DateTime;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class PricelistPrefixPriceController extends JsonController
{
    public function actionGet()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            PricelistPrefixPrice::find()
                ->where(['id' => $this->request['id']])
                ->asArray()
                ->one();
    }
    
    public function actionRead()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $pageNumber = $this->request['page_number'];
        $offset = ($pageNumber - 1) * PricelistPrefixPrice::PAGE_LIMIT;
        $limit = PricelistPrefixPrice::PAGE_LIMIT;
        
        return
            PricelistPrefixPrice::find()
                ->where(['pricelist_filter_b_id' => $this->request['pricelist_filter_b_id']])
                ->andWhere('date_to > now()')
                ->orderBy('prefix_b')
                ->offset($offset)
                ->limit($limit)
                ->asArray()
                ->all();
    }
    
    public function actionReadPage()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $pageNumber = $this->request['page_number'];
        $offset = ($pageNumber - 1) * PricelistPrefixPrice::PAGE_LIMIT;
        $limit = PricelistPrefixPrice::PAGE_LIMIT;
        
        $query = <<<SQL
        select * from billing_uu.pricelist_prefix_price ppp
        where pricelist_filter_b_id = :b_id
        and date_to > now()
        and prefix_b in (
            select distinct prefix_b from billing_uu.pricelist_prefix_price
            where pricelist_filter_b_id = :b_id
            and date_to > now()
            order by prefix_b
            offset :offset
            limit :limit
        );
SQL;
        
        $result = PricelistPrefixPrice::findBySql($query, [':b_id' => $this->request['pricelist_filter_b_id'], ':offset' => $offset, ':limit' => $limit])
            ->asArray()
            ->all();
        
        return $result;
    }

    public function actionReadAll()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $query = <<<SQL
        select * from billing_uu.pricelist_prefix_price ppp
        where pricelist_filter_b_id = :b_id
        and date_to > now()
        and prefix_b in (
            select distinct prefix_b from billing_uu.pricelist_prefix_price
            where pricelist_filter_b_id = :b_id
            and date_to > now()
            order by prefix_b
        );
    SQL;
    
    $result = PricelistPrefixPrice::findBySql($query, [':b_id' => $this->request['pricelist_filter_b_id']])
        ->asArray()
        ->all();
    
    return $result;
}

    
    public function actionSave()
    {
        if (!\Yii::$app->user->can('pricelist_edit') && !\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $prefixB = $this->request['prefix_b'];
    
        if (preg_match("/[^\d,\s]/", $prefixB)) {
            return [
                'error' => 'Некорректный формат префикса! Допустимы только цифры и запятая.',
                'field' => 'prefix_b'
            ];
        }

        $dateFrom = $this->request['date_from'];
        $dateTo = $this->request['date_to'];
        
        $dtFrom = DateTime::createFromFormat("Y-m-d", $dateFrom);
        if ($dtFrom === false || array_sum($dtFrom::getLastErrors())) {
            return ['error' => 'Некорректный формат даты!', 'field' => 'date_from'];
        }
        
        $dtTo = DateTime::createFromFormat("Y-m-d", $dateTo);
        if ($dtTo === false || array_sum($dtTo::getLastErrors())) {
            return ['error' => 'Некорректный формат даты!', 'field' => 'date_to'];
        }
        
        if ($dtFrom >= $dtTo) {
            return ['error' => 'Дата окончания должна быть больше, чем дата начала!', 'field' => 'date_to'];
        }
        
        $filterBId = $this->request['pricelist_filter_b_id'];
        $id = isset($this->request['id']) ? $this->request['id'] : null;
        $priceRequest = $this->request['b_number_price'];
        $pricelistIsActive = $this->request['pricelist_is_active'];
        $pricelistId = $this->request['pricelist_id'];
        
        $transaction = PricelistPrefixPrice::getDb()->beginTransaction();
        try {
            if (strpos($prefixB, ',') !== false) {
                $prefixBArray = explode(',', $prefixB);
                
                $historyObject = PricelistPrefixPriceHistory::createHistory($filterBId, $pricelistId, $dateFrom, $dateTo, count($prefixBArray), 'add');
                
                foreach ($prefixBArray as $prefixB) {
                    $prefixB = trim($prefixB);
                    $saveResult = $this->saveSingle($id, $prefixB, $filterBId, $dateFrom, $dateTo, $priceRequest, $pricelistIsActive, $pricelistId, $historyObject->id);
                    
                    if (isset($saveResult['error'])) {
                        return $saveResult;
                    }
                }
            } else {
                $historyObject = PricelistPrefixPriceHistory::createHistory($filterBId, $pricelistId, $dateFrom, $dateTo, 1, 'add');
                
                $prefixB = trim($prefixB);
                $saveResult = $this->saveSingle($id, $prefixB, $filterBId, $dateFrom, $dateTo, $priceRequest, $pricelistIsActive, $pricelistId, $historyObject->id);
                
                if (isset($saveResult['error'])) {
                    return $saveResult;
                }
            }
            
            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
        
        return ['success' => 1];
    }
    
    private function saveSingle($id, $prefixB, $filterBId, $dateFromRequest, $dateToRequest, $priceRequest, $pricelistIsActive, $pricelistId, $historyId)
    {
        if ($pricelistIsActive && !isset($this->request['id'])) {
            //create----------------------------------------------------------------------------------------------------
            $pricelist = $this->getPricelistOr404($pricelistId);
    
            $dateFromNew = strtotime($dateFromRequest);
            $dateNow = strtotime(date('Y-m-d'));
            $dateFromPricelist = strtotime($pricelist->date_start);
    
            $dateToCompare = ($dateNow > $dateFromPricelist) ? $dateNow : $dateFromPricelist;
    
            if ($dateFromNew < $dateToCompare) {
                return [
                    'error' => 'Дата активации прайса префикса должна быть не раньше, чем сегодня, и не раньше даты активации прайслиста!',
                    'field' => 'date_from'
                ];
            }
    
            $result = PricelistPrefixPrice::checkIfPrefixDateExists($prefixB, $filterBId, $dateFromRequest);
    
            if ($result) {
                return ['error' => 'В этом фильтре B уже есть такой префикс (' . $prefixB . ') c такой датой (' . $dateFromRequest . ')', 'field' => 'date_from'];
            }
    
            $item = null;
            
        } elseif ($pricelistIsActive && isset($this->request['id'])) {
            //edit----------------------------------------------------------------------------------------------------
            $pricelist = $this->getPricelistOr404($pricelistId);
            $item = $this->getPricelistPrefixPriceOr404($id);
            
            $dateFromNew = strtotime($dateFromRequest);
            $dateNow = strtotime(date('Y-m-d'));
            $dateFromPricelist = strtotime($pricelist->date_start);
            $dateFromPrefixPrice = strtotime($item->date_from);
            
            $dateToCompare = ($dateNow > $dateFromPricelist) ? $dateNow : $dateFromPricelist;

            if ($dateFromNew < $dateToCompare) {
                return [
                    'error' => 'Дата активации прайса префикса должна быть не раньше, чем сегодня, и не раньше даты активации прайслиста!',
                    'field' => 'date_from'
                ];
            }
            
            $result = PricelistPrefixPrice::checkIfPrefixDateExists($prefixB, $filterBId, $dateFromRequest, $id);
    
            if ($result) {
                return ['error' => 'В этом фильтре B уже есть такой префикс (' . $prefixB . ') c такой датой (' . $dateFromRequest . ')', 'field' => 'date_from'];
            }
            
            if ($dateFromPrefixPrice !== $dateFromNew) {
                $item = null;
            }
        } elseif (!$pricelistIsActive && !isset($this->request['id'])) {
            //create----------------------------------------------------------------------------------------------------
            $pricelist = $this->getPricelistOr404($pricelistId);
    
            $dateFromNew = strtotime($dateFromRequest);
            $dateNow = strtotime(date('Y-m-d'));
            $dateFromPricelist = strtotime($pricelist->date_start);
    
            $dateToCompare = ($dateNow > $dateFromPricelist) ? $dateNow : $dateFromPricelist;
    
            if ($dateFromNew < $dateToCompare) {
                return [
                    'error' => 'Дата активации прайса префикса должна быть не раньше, чем сегодня, и не раньше даты активации прайслиста!',
                    'field' => 'date_from'
                ];
            }
    
            $result = PricelistPrefixPrice::checkIfPrefixDateExists($prefixB, $filterBId, $dateFromRequest);
    
            if ($result) {
                return ['error' => 'В этом фильтре B уже есть такой префикс (' . $prefixB . ') c такой датой (' . $dateFromRequest . ')', 'field' => 'date_from'];
            }
    
            $item = null;
            
        } elseif (!$pricelistIsActive && isset($this->request['id'])) {
            //edit------------------------------------------------------------------------------------------------------
            $pricelist = $this->getPricelistOr404($pricelistId);
            $item = $this->getPricelistPrefixPriceOr404($id);
    
            $dateFromNew = strtotime($dateFromRequest);
            $dateNow = strtotime(date('Y-m-d'));
            $dateFromPricelist = strtotime($pricelist->date_start);
            $dateFromPrefixPrice = strtotime($item->date_from);
            
            $dateToCompare = ($dateNow > $dateFromPricelist) ? $dateNow : $dateFromPricelist;
    
            if ($dateFromNew < $dateToCompare) {
                return [
                    'error' => 'Дата активации прайса префикса должна быть не раньше, чем сегодня, и не раньше даты активации прайслиста!',
                    'field' => 'date_from'
                ];
            }
    
            $result = PricelistPrefixPrice::checkIfPrefixDateExists($prefixB, $filterBId, $dateFromRequest, $id);
    
            if ($result) {
                return ['error' => 'В этом фильтре B уже есть такой префикс (' . $prefixB . ') c такой датой (' . $dateFromRequest . ')', 'field' => 'date_from'];
            }
            
            if ($dateFromPrefixPrice !== $dateFromNew) {
                $item = null;
            }
        }
        
        $data = [
            'prefix_b' => $prefixB,
            'pricelist_filter_b_id' => $filterBId,
            'date_from' => $dateFromRequest,
            'date_to' => $dateToRequest,
            'b_number_price' => $priceRequest
        ];
        
        if (!$item) {
            $item = PricelistPrefixPrice::create($data, $historyId);
        } else {
            $historyItem = [
                'pricelist_prefix_price_history_id' => $historyId,
                'prefix_b' => $data['prefix_b'],
                'price_old' => $item->b_number_price,
                'price_new' => $data['b_number_price'],
                'date_from' => $data['date_from'],
                'date_to' => $data['date_to']
            ];
            
            if ($item->b_number_price < $data['b_number_price']) {
                $historyItem['type'] = 'increase';
            } elseif ($item->b_number_price > $data['b_number_price']) {
                $historyItem['type'] = 'decrease';
            } elseif ($data['date_to'] == '3000-01-01') {
                $historyItem['type'] = 'prolong';
            } else {
                $historyItem['type'] = 'delete';
            }
            
            $item->load($data, '');
            
            $historyItemObject = PricelistPrefixPriceHistoryItem::create($historyItem);
            $historyItemObject->save();
        }

        if (!$item->save()) {
            throw new FormValidationException($item);
        }
    }
    
    public function actionSingleHistory()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $prefixB = $this->request['prefix_b'];
        
        if (empty(trim($prefixB))) {
            $andWhere = ['prefix_b' => ''];
        } else {
            $andWhere = ['prefix_b' => trim($prefixB)];
        }
        
        return
            PricelistPrefixPrice::find()
                ->select(['id', 'pricelist_filter_b_id', 'prefix_b', 'b_number_price', 'date_from', 'date_to'])
                ->where(['pricelist_filter_b_id' => $this->request['pricelist_filter_b_id']])
                ->andWhere($andWhere)
                ->orderBy('id desc')
                ->asArray()
                ->all();
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
        
        $item = $this->getPricelistPrefixPriceOr404($this->request['id']);
        $item->delete();
        
        return ['success' => 1];
    }
}
