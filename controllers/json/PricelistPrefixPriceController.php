<?php

namespace app\controllers\json;

use app\models\billing_uu\PricelistPrefixPrice;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
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
        
        $dt = DateTime::createFromFormat("Y-m-d", $dateFrom);
        if ($dt === false || array_sum($dt::getLastErrors())) {
            return ['error' => 'Некорректный формат даты!', 'field' => 'date_from'];
        }
        
        $dt = DateTime::createFromFormat("Y-m-d", $dateTo);
        if ($dt === false || array_sum($dt::getLastErrors())) {
            return ['error' => 'Некорректный формат даты!', 'field' => 'date_to'];
        }
        
        $filterBId = $this->request['pricelist_filter_b_id'];
        $id = isset($this->request['id']) ? $this->request['id'] : null;
        $priceRequest = $this->request['b_number_price'];
        $pricelistIsActive = $this->request['pricelist_is_active'];
        $pricelistId = $this->request['pricelist_id'];
        
        if (strpos($prefixB, ',') !== false) {
            $prefixBArray = explode(',', $prefixB);
            
            foreach ($prefixBArray as $prefixB) {
                $prefixB = trim($prefixB);
                $saveResult = $this->saveSingle($id, $prefixB, $filterBId, $dateFrom, $dateTo, $priceRequest, $pricelistIsActive, $pricelistId);
                
                if (isset($saveResult['error'])) {
                    return $saveResult;
                }
            }
        } else {
            $prefixB = trim($prefixB);
            return $this->saveSingle($id, $prefixB, $filterBId, $dateFrom, $dateTo, $priceRequest, $pricelistIsActive, $pricelistId);
        }
    }
    
    private function saveSingle($id, $prefixB, $filterBId, $dateFromRequest, $dateToRequest, $priceRequest, $pricelistIsActive, $pricelistId)
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
    
            $item = PricelistPrefixPrice::create();
            
        } elseif ($pricelistIsActive && isset($this->request['id'])) {
            //edit----------------------------------------------------------------------------------------------------
            $pricelist = $this->getPricelistOr404($pricelistId);
            $item = $this->getPricelistPrefixPriceOr404($id);
            
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
            
            $result = PricelistPrefixPrice::checkIfPrefixDateExists($prefixB, $filterBId, $dateFromRequest, $id);
    
            if ($result) {
                return ['error' => 'В этом фильтре B уже есть такой префикс (' . $prefixB . ') c такой датой (' . $dateFromRequest . ')', 'field' => 'date_from'];
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
    
            $item = PricelistPrefixPrice::create();
            
        } elseif (!$pricelistIsActive && isset($this->request['id'])) {
            //edit------------------------------------------------------------------------------------------------------
            $pricelist = $this->getPricelistOr404($pricelistId);
            $item = $this->getPricelistPrefixPriceOr404($id);
    
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
    
            $result = PricelistPrefixPrice::checkIfPrefixDateExists($prefixB, $filterBId, $dateFromRequest, $id);
    
            if ($result) {
                return ['error' => 'В этом фильтре B уже есть такой префикс (' . $prefixB . ') c такой датой (' . $dateFromRequest . ')', 'field' => 'date_from'];
            }
        }
        
        $item->load([
            'prefix_b' => $prefixB,
            'pricelist_filter_b_id' => $filterBId,
            'date_from' => $dateFromRequest,
            'date_to' => $dateToRequest,
            'b_number_price' => $priceRequest
        ], '');
    
        $transaction = PricelistPrefixPrice::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }
        
            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
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
    }
}
