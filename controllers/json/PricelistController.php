<?php

namespace app\controllers\json;

use app\models\billing_uu\Pricelist;
use app\models\billing_uu\PricelistFilterB;
use app\models\billing_uu\PricelistPrefixPrice;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\billing_uu\PricelistFilterA;
use app\models\billing_uu\PricelistLocation;
use yii\base\Exception;
use yii\db\Expression;
use yii\db\IntegrityException;
use yii\db\Query;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class PricelistController extends JsonController
{
    const SEARCH_LIMIT_PER_PRICELIST = 5;
    
    public function actionList()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        return
            Pricelist::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }
    
    public function actionRead()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $searchArray = $this->request['search_array'];
        $limit = $this->request['limit'];
        $offset = $this->request['offset'];
        
        $query = Pricelist::find()
            ->alias('p')
            ->select([
                'p.*',
                'g.name as group_name',
                'is_in_use' => new Expression('sum(case when atl.id is not null then 1 else 0 end) > 0')
            ])
            ->leftJoin('billing_uu.pricelist_group g', 'g.id = p.pricelist_group_id')
            ->leftJoin('billing_uu.package_pricelist pp', 'pp.nnp_pricelist_id = p.id')
            ->leftJoin('billing_uu.account_tariff_light atl', 'atl.tariff_id = pp.tariff_id')
            ->orderBy('name')
            ->groupBy('p.id, g.id')
            ->limit($limit)
            ->offset($offset)
            ->asArray();
        
        $countQuery = Pricelist::find()
            ->select(['id'])
            ->distinct();
        
        if (isset($searchArray['group_id']) && $searchArray['group_id'] && $searchArray['group_id'] != 'all') {
            $query->where(['p.pricelist_group_id' => $searchArray['group_id']]);
            $countQuery->where(['pricelist_group_id' => $searchArray['group_id']]);
        }
        
        if (isset($searchArray['service_type_id']) && $searchArray['service_type_id']) {
            $query->andWhere(['p.service_type_id' => $searchArray['service_type_id']]);
            $countQuery->andWhere(['service_type_id' => $searchArray['service_type_id']]);
        }
    
        if (isset($searchArray['is_active']) && is_bool($searchArray['is_active'])) {
            $query->andWhere(['p.is_active' => $searchArray['is_active']]);
            $countQuery->andWhere(['is_active' => $searchArray['is_active']]);
        }
    
        if (isset($searchArray['is_orig']) && is_bool($searchArray['is_orig'])) {
            $query->andWhere(['p.orig' => $searchArray['is_orig']]);
            $countQuery->andWhere(['orig' => $searchArray['is_orig']]);
        }
        
        if (isset($searchArray['id']) && $searchArray['id']) {
            $query->andWhere(['p.id' => $searchArray['id']]);
            $countQuery->andWhere(['id' => $searchArray['id']]);
        }
    
        if (isset($searchArray['query']) && $searchArray['query']) {
            $query->andWhere('p.name ilike :name');
            $query->addParams([':name' => '%' . $searchArray['query'] . '%']);
            $countQuery->andWhere('name ilike :name');
            $countQuery->addParams([':name' => '%' . $searchArray['query'] . '%']);
        }
        
        $data = $query->all();
    
        $count = $countQuery->count();
        
        return [
            'totalCount' => $count,
            'data' => $data
        ];
    }
    
    public function actionGetWithDependentsNew()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $flatRules = [
            'p' => Pricelist::rulesFlat(),
            'pl' => PricelistLocation::rulesFlat(),
            'pfa' => PricelistFilterA::rulesFlat(),
            'pfb' => PricelistFilterB::rulesFlat(),
            'ppp' => PricelistPrefixPrice::rulesFlat(),
        ];
        
        $select = [];
        
        foreach ($flatRules as $tableKey => $rulesArray) {
            foreach ($rulesArray as $rule) {
                $select[$tableKey . '__' . $rule] = $tableKey . '.' . $rule;
            }
        }
        
        $queryResult = 
            Pricelist::find()
                ->alias('p')
                ->select($select)
                ->leftJoin(PricelistLocation::tableName() . ' as pl', 'pl.pricelist_id = p.id')
                ->leftJoin(PricelistFilterA::tableName() . ' as pfa', 'pfa.pricelist_location_id = pl.id')
                ->leftJoin(PricelistFilterB::tableName() . ' as pfb', 'pfb.pricelist_filter_a_id = pfa.id')
                ->leftJoin(PricelistPrefixPrice::tableName() . ' as ppp', 'ppp.pricelist_filter_b_id = pfb.id')
                ->where(['p.id' => $this->request['id']])
                ->andWhere('ppp.date_to > now()')
                ->orderBy('pl.id, pfa.id, pfb.id, ppp.prefix_b, ppp.id')
                ->asArray()
                ->all();

        $locationsProcessed = [];
        $filtersAProcessed = [];
        $filtersBProcessed = [];
        
        $mccIdArray = [];
        $simImsiPartnerIdArray = [];
        $simImsiProfileIdArray = [];
        $nnpCountryIdArray = [];
        $nnpDestinationIdArray = [];
        $nnpOperatorIdArray = [];
        $nnpRegionIdArray = [];
        $nnpCityIdArray = [];
        $nnpNdcTypeIdArray = [];
        
        $idArrays = [
            'nnp.mcc' => ['ids' => &$mccIdArray, 'name_field' => 'country', 'id_field' => 'mcc'],
            'billing_uu.sim_imsi_profile' => ['ids' => &$simImsiProfileIdArray, 'name_field' => 'name', 'id_field' => 'id'],
            'billing_uu.sim_imsi_partner' => ['ids' => &$simImsiPartnerIdArray, 'name_field' => 'name', 'id_field' => 'id'],
            'nnp.country' => ['ids' => &$nnpCountryIdArray, 'name_field' => 'name_rus', 'id_field' => 'code'],
            'nnp.destination' => ['ids' => &$nnpDestinationIdArray, 'name_field' => 'name', 'id_field' => 'id'],
            'nnp.operator' => ['ids' => &$nnpOperatorIdArray, 'name_field' => 'name', 'id_field' => 'id'],
            'nnp.region' => ['ids' => &$nnpRegionIdArray, 'name_field' => 'name', 'id_field' => 'id'],
            'nnp.city' => ['ids' => &$nnpCityIdArray, 'name_field' => 'name', 'id_field' => 'id'],
            'nnp.ndc_type' => ['ids' => &$nnpNdcTypeIdArray, 'name_field' => 'name', 'id_field' => 'id']
        ];
        
        foreach ($queryResult as $queryItem) {
            if (!in_array($queryItem['pl__id'], $locationsProcessed)) {
                self::processQueryArray($mccIdArray, $queryItem['pl__mcc']);
                self::processQueryArray($simImsiPartnerIdArray, $queryItem['pl__sim_partner']);
                self::processQueryArray($simImsiProfileIdArray, $queryItem['pl__sim_profile']);
                
                $locationsProcessed[] = $queryItem['pl__id'];
            }
            
            if (!in_array($queryItem['pfa__id'], $filtersAProcessed)) {
                self::processQueryArray($nnpCountryIdArray, $queryItem['pfa__nnp_country']);
                self::processQueryArray($nnpDestinationIdArray, $queryItem['pfa__nnp_destination']);
                self::processQueryArray($nnpOperatorIdArray, $queryItem['pfa__nnp_operator']);
                self::processQueryArray($nnpRegionIdArray, $queryItem['pfa__nnp_region']);
                self::processQueryArray($nnpCityIdArray, $queryItem['pfa__nnp_city']);
                self::processQueryArray($nnpNdcTypeIdArray, $queryItem['pfa__nnp_ndc_type']);
                
                $filtersAProcessed[] = $queryItem['pfa__id'];
            }
            
            if (!in_array($queryItem['pfb__id'], $filtersBProcessed)) {
                self::processQueryArray($nnpCountryIdArray, $queryItem['pfb__nnp_country']);
                self::processQueryArray($nnpDestinationIdArray, $queryItem['pfb__nnp_destination']);
                self::processQueryArray($nnpOperatorIdArray, $queryItem['pfb__nnp_operator']);
                self::processQueryArray($nnpRegionIdArray, $queryItem['pfb__nnp_region']);
                self::processQueryArray($nnpCityIdArray, $queryItem['pfb__nnp_city']);
                self::processQueryArray($nnpNdcTypeIdArray, $queryItem['pfb__nnp_ndc_type']);
                
                $filtersBProcessed[] = $queryItem['pfb__id'];
            }
        }
        
        foreach ($idArrays as $key => &$item) {
            $item['ids'] = array_unique($item['ids']);
            
            if (count($item['ids'])) {
                $tempIds = (new Query())->select(['id' => $item['id_field'], 'name' => $item['name_field']])->from($key)->where([$item['id_field'] => $item['ids']])->all();
                $item['ids'] = [];
                foreach ($tempIds as $tempId) {
                    $item['ids'][$tempId['id']] = $tempId['name'];
                }
            }
        }
        
        $result = [];
        $counter = 0;
        $locationKey = 0;
        $filterAKey = 0;
        $filterBKey = 0;
        
        foreach ($queryResult as $queryItem) {
            if (empty($result)) {
                $result[0] = self::createPricelistRow($queryItem);
                $counter++;
            }
            
            if (empty($queryItem['pl__id'])) {
                continue;
            }
            
            if (!isset($result[$locationKey]['is_location']) || ($result[$locationKey]['is_location'] && $result[$locationKey]['id'] != $queryItem['pl__id'])) {
                $result[$counter] = self::createLocationRow($queryItem, $idArrays);
                $locationKey = $counter;
                $counter++;
            }
            
            if (empty($queryItem['pfa__id'])) {
                continue;
            }
            
            if (!isset($result[$filterAKey]['is_filter_a_header']) || ($result[$filterAKey]['is_filter_a_header'] && $result[$filterAKey]['filter_a_id'] != $queryItem['pfa__id'])) {
                $realCount = (new Query())
                    ->select('prefix_b')
                    ->distinct()
                    ->from('billing_uu.pricelist_prefix_price')
                    ->where('pricelist_filter_b_id = :b_id')
                    ->andWhere('date_to > now()')
                    ->addParams([':b_id' => $queryItem['pfb__id']])
                    ->count();
                
                if ($realCount > PricelistPrefixPrice::PAGE_LIMIT) {
                    $count = PricelistPrefixPrice::PAGE_LIMIT + 1;
                } else {
                    $count = $realCount;
                }
                
                $result[$counter] = self::createFilterAFilterBPrefixRow($queryItem, $idArrays, $count, $realCount);
                $filterAKey = $counter;
                $filterBKey = $counter;
                $counter++;
            }
            
            if (empty($queryItem['pfb__id'])) {
                continue;
            }
            
            if (!isset($result[$filterBKey]['is_filter_b_header']) || ($result[$filterBKey]['is_filter_b_header'] && $result[$filterBKey]['filter_b_id'] != $queryItem['pfb__id'])) {
                $realCount = (new Query())
                    ->select('prefix_b')
                    ->distinct()
                    ->from('billing_uu.pricelist_prefix_price')
                    ->where('pricelist_filter_b_id = :b_id')
                    ->andWhere('date_to > now()')
                    ->addParams([':b_id' => $queryItem['pfb__id']])
                    ->count();
                
                if ($realCount > PricelistPrefixPrice::PAGE_LIMIT) {
                    $count = PricelistPrefixPrice::PAGE_LIMIT + 1;
                } else {
                    $count = $realCount;
                }
                
                $result[$counter] = self::createFilterBPrefixRow($queryItem, $idArrays, $count, $realCount);
                $filterBKey = $counter;
                $counter++;
                $result[$filterAKey]['total_prefix_count'] += $count;
            }
            
            if (empty($queryItem['ppp__id'])) {
                continue;
            }
            
            if ($result[$counter - 1]['prefixes'][0]['prefix_price_id'] != $queryItem['ppp__id']) {
                $isPrefixSet = false;
                
                for ($i = $filterBKey; $i < $counter; $i++) {
                    if ($result[$i]['prefix_b'] == $queryItem['ppp__prefix_b'] . ' ') {
                        $result[$i]['prefixes'][] = self::createPrefixItem($queryItem, $result[$i]['prefixes']);
                        $isPrefixSet = true;
                        break;
                    }
                }
                
                if (!$isPrefixSet && ($counter - $filterBKey) < PricelistPrefixPrice::PAGE_LIMIT) {
                    $result[$counter] = self::createPrefixRow($queryItem);
                    $counter++;
                } elseif (($counter - $filterBKey) == PricelistPrefixPrice::PAGE_LIMIT) {
                    $count = (new Query())
                        ->select('prefix_b')
                        ->distinct()
                        ->from('billing_uu.pricelist_prefix_price')
                        ->where('pricelist_filter_b_id = :b_id')
                        ->andWhere('date_to > now()')
                        ->addParams([':b_id' => $queryItem['pfb__id']])
                        ->count();
                    
                    $result[$counter] = self::createPrefixFooterRow($queryItem, $result[$filterBKey]['filter_b_id'], $count);
                    $counter++;
                }
            }
        }

        return $result;
    }
    
    private static function createPricelistRow($item)
    {
        return [
            'id' => $item['p__id'],
            'is_pricelist' => true,
            'name' => isset($item['p__description']) ? $item['p__description'] . ', валюта ' . $item['p__currency_id'] : 'Валюта ' . $item['p__currency_id'],
            'date_created' => $item['p__date_created'],
            'date_start' => $item['p__date_start'],
            'is_active' => $item['p__is_active'],
            'orig' => $item['p__orig'],
            'service_type_id' => $item['p__service_type_id']
        ];
    }
    
    private static function createLocationRow($item, $idArrays)
    {
        $isBasic = ($item['pl__id'] == $item['p__basic_pricelist_location_id']);
                
        $locationText = self::formLocationText($item, $isBasic, $idArrays);
        
        return [
            'is_location' => true,
            'id' => $item['pl__id'],
            'has_location_mark' => false,
            'location_text' => $locationText,
            'has_children' => isset($item['pfa__id']),
            'is_basic' => $isBasic
        ];
    }
    
    private static function createFilterAFilterBPrefixRow($item, $idArrays, $count, $realCount)
    {
        return [
            'is_filter_b_header' => true,
            'is_filter_a_header' => true,
            'has_filter_a_mark' => false,
            'has_filter_b_mark' => false,
            'filter_a_name' => self::formFilterText($item, 'pfa__', $idArrays),
            'filter_b_name' => self::formFilterText($item, 'pfb__', $idArrays),
            'filter_b_rating' => (($item['pfb__rating'] == 1) ? '' : $item['pfb__rating']),
            'filter_b_use_for_minimum' => $item['pfb__use_for_minimum'],
            'filter_a_id' => $item['pfa__id'],
            'filter_b_id' => $item['pfb__id'],
            'is_prefix_price' => true,
            'prefix_b' => $item['ppp__prefix_b'] . ' ',
            'prefix_count' => $count,
            'total_prefix_count' => $realCount,
            'total_pagination_count' => $realCount,
            'interconnect_price' => empty($item['pfb__interconnect_price']) ? 0 : floatval($item['pfb__interconnect_price']),
            'prefixes' => [
                [
                    'prefix_price_id' => $item['ppp__id'],
                    'has_prefix_mark' => false,
                    'b_number_price' => $item['ppp__b_number_price'],
                    'date_from' => $item['ppp__date_from'],
                    'date_to' => $item['ppp__date_to'],
                    'price_change' => 'none'
                ]
            ]
        ];
    }
    
    private static function createFilterBPrefixRow($item, $idArrays, $count, $realCount)
    {
        return [
            'is_filter_b_header' => true,
            'is_filter_a_header' => false,
            'has_filter_a_mark' => false,
            'has_filter_b_mark' => false,
            'filter_b_name' => self::formFilterText($item, 'pfb__', $idArrays),
            'filter_b_rating' => (($item['pfb__rating'] == 1) ? '' : $item['pfb__rating']),
            'filter_b_use_for_minimum' => $item['pfb__use_for_minimum'],
            'filter_a_id' => $item['pfa__id'],
            'filter_b_id' => $item['pfb__id'],
            'is_prefix_price' => true,
            'prefix_b' => $item['ppp__prefix_b'] . ' ',
            'prefix_count' => $count,
            'total_pagination_count' => $realCount,
            'interconnect_price' => empty($item['pfb__interconnect_price']) ? 0 : floatval($item['pfb__interconnect_price']),
            'prefixes' => [
                [
                    'prefix_price_id' => $item['ppp__id'],
                    'has_prefix_mark' => false,
                    'b_number_price' => $item['ppp__b_number_price'],
                    'date_from' => $item['ppp__date_from'],
                    'date_to' => $item['ppp__date_to'],
                    'price_change' => 'none'
                ]
            ]
        ];
    }
    
    private static function createPrefixRow($item)
    {
        return [
            'is_filter_b_header' => false,
            'is_filter_a_header' => false,
            'is_prefix_price' => true,
            'prefix_b' => $item['ppp__prefix_b'] . ' ',
            'prefixes' => [
                [
                    'prefix_price_id' => $item['ppp__id'],
                    'has_prefix_mark' => false,
                    'b_number_price' => $item['ppp__b_number_price'],
                    'date_from' => $item['ppp__date_from'],
                    'date_to' => $item['ppp__date_to'],
                    'price_change' => 'none'
                ]
            ]
        ];
    }
    
    private static function createPrefixItem($item, $previous)
    {
        $previousPrice = $previous[count($previous) - 1]['b_number_price'];
        
        return [
            'prefix_price_id' => $item['ppp__id'],
            'has_prefix_mark' => false,
            'b_number_price' => $item['ppp__b_number_price'],
            'date_from' => $item['ppp__date_from'],
            'date_to' => $item['ppp__date_to'],
            'price_change' => ($previousPrice > $item['ppp__b_number_price'] ? 'decrease' : ($previousPrice == $item['ppp__b_number_price'] ? 'none' : 'increase'))
        ];
    }
    
    private static function createPrefixFooterRow($item, $filterBId, $count)
    {
        return [
            'is_filter_b_header' => false,
            'is_filter_a_header' => false,
            'is_prefix_price' => false,
            'is_prefix_price_footer' => true,
            'totalCount' => $count,
            'currentPage' => 1,
            'offset' => 0,
            'filter_b_id' => $filterBId,
            'prefixes' => [['prefix_price_id' => 'junk']],
            'prefix_b' => 'junk'
        ];
    }
    
    private static function processQueryArray(&$idArray, $queryItemElement)
    {
        if (empty($queryItemElement) || $queryItemElement == '{}') {
            return;
        }

        $cleanedElement = str_replace(['{', '}'], '', $queryItemElement);
        $explodedElement = explode(',', $cleanedElement);
        $idArray = array_merge($idArray, $explodedElement);
    }
    
    private static function formLocationText($item, $isBasic, $idArrays)
    {
        $locationText = '';
        
        $mcc = self::getNameFromDictionary($item['pl__mcc'], $idArrays['nnp.mcc']['ids']);
        $simPartner = self::getNameFromDictionary($item['pl__sim_partner'], $idArrays['billing_uu.sim_imsi_partner']['ids']);
        $simProfile = self::getNameFromDictionary($item['pl__sim_profile'], $idArrays['billing_uu.sim_imsi_profile']['ids']);
        
        if (!empty($item['pl__mcc']) && $item['pl__mcc'] != '{}' && !empty($item['pl__mnc'] && $item['pl__mnc'] != '{}')) {
            $tempMcc = str_replace(['{', '}'], '', $item['pl__mcc']);
            $tempMcc = explode(',', $tempMcc);
            $tempMnc = str_replace(['{', '}'], '', $item['pl__mnc']);
            $tempMnc = explode(',', $tempMnc);
            $mncArray = (new Query())->select('network')->from('nnp.mnc')->where(['mcc' => $tempMcc, 'mnc' => $tempMnc])->all();
            $mncFormattedArray = [];
            foreach ($mncArray as $mncItem) {
                $mncFormattedArray[] = $mncItem['network'];
            }
            $mnc = implode(', ', $mncFormattedArray);
        }
        
        $locationText = isset($item['pl__description']) ? $item['pl__description'] : 
            ((($isBasic ? 'Базовое местоположение: ' : 'Местоположение: ') . PricelistLocation::LOCATION_TYPE_NAMES[$item['pl__location_id']]) . 
            (empty($mcc) ? '' : ('; MCC: ' . $mcc)) . (empty($mnc) ? '' : ('; MNC: ' . $mnc)) . 
            (empty($item['pl__delta_price']) ? '' : '; Наценка: ' . $item['pl__delta_price']) . 
            (empty($simPartner) ? '' : ('; Sim Партнер: ' . $simPartner)) . (empty($simProfile) ? '' : ('; Sim Профиль: ' . $simProfile)));
        
        return $locationText;
    }
    
    private static function formFilterText($item, $prefix, $idArrays)
    {
        $filterText = '';

        if ($item[$prefix . 'nnp_country'] == '{}' && $item[$prefix . 'f_inv_nnp_country'] ||
            $item[$prefix . 'nnp_city'] == '{}' && $item[$prefix . 'f_inv_nnp_city'] ||
            $item[$prefix . 'nnp_destination'] == '{}' && $item[$prefix . 'f_inv_nnp_destination'] ||
            $item[$prefix . 'nnp_region'] == '{}' && $item[$prefix . 'f_inv_nnp_region'] ||
            $item[$prefix . 'nnp_ndc_type'] == '{}' && $item[$prefix . 'f_inv_nnp_ndc_type'] ||
            $item[$prefix . 'nnp_operator'] == '{}' && $item[$prefix . 'f_inv_nnp_operator']) {
            $filterText = 'Запрещено все!';
        } else {
            $countryName = self::getNameFromDictionary($item[$prefix . 'nnp_country'], $idArrays['nnp.country']['ids']);
            $ndcTypeName = self::getNameFromDictionary($item[$prefix . 'nnp_ndc_type'], $idArrays['nnp.ndc_type']['ids']);
            $operatorName = self::getNameFromDictionary($item[$prefix . 'nnp_operator'], $idArrays['nnp.operator']['ids']);
            $regionName = self::getNameFromDictionary($item[$prefix . 'nnp_region'], $idArrays['nnp.region']['ids']);
            $cityName = self::getNameFromDictionary($item[$prefix . 'nnp_city'], $idArrays['nnp.city']['ids']);
            $destinationName = self::getNameFromDictionary($item[$prefix . 'nnp_destination'], $idArrays['nnp.destination']['ids']);
            
            $filterText = (empty($countryName) ? '' : ($item[$prefix . 'f_inv_nnp_country'] ? ('Кроме: ' . $countryName) : $countryName)) .
                (empty($ndcTypeName) ? '' : ($item[$prefix . 'f_inv_nnp_ndc_type'] ? (' Кроме: ' . $ndcTypeName) : (' ' . $ndcTypeName))) .
                (empty($operatorName) ? '' : ($item[$prefix . 'f_inv_nnp_operator'] ? (' Кроме: ' . $operatorName) : (' ' . $operatorName))) .
                (empty($regionName) ? '' : ($item[$prefix . 'f_inv_nnp_region'] ? (' Кроме: ' . $regionName) : (' ' . $regionName))) .
                (empty($cityName) ? '' : ($item[$prefix . 'f_inv_nnp_city'] ? (' Кроме: ' . $cityName) : (' ' . $cityName))) . 
                (empty($item[$prefix . 'regex']) ? '' : ' Regex: ' . $item[$prefix . 'regex']);

            if (!$filterText) {
                $filterText = $item[$prefix . 'f_inv_nnp_destination'] ? ('Кроме: ' . $destinationName) : $destinationName;
            }
        }
        
        if ($item[$prefix . 'description'] && $filterText) {
            $filterText = $item[$prefix . 'description'] . ' (' . $filterText . ')';
        } else if (!$item[$prefix . 'description'] && $filterText) {
            // do_nothing
        } else if ($item[$prefix . 'description'] && !$filterText) {
            $filterText = $item[$prefix . 'description'];
        } else {
            $filterText = '--';
        }
        
        return $filterText;
    }
    
    private static function getNameFromDictionary($idString, $dictionary)
    {
        if (empty($idString) || $idString == '{}') {
            return '';
        }
        
        $idStringCleaned = str_replace(['{', '}'], '', $idString);
        $idArray = explode(',', $idStringCleaned);
        $nameArray = [];
        
        foreach ($idArray as $id) {
            if (isset($dictionary[$id])) {
                $nameArray[] = $dictionary[$id];
            }
        }
        
        return implode(', ', $nameArray);
    }
    
    public function actionGetWithDependents()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            Pricelist::find()
                ->with('location.filterA.filterB.prefixPrice')
                ->with('location.filterA.filterB.prefixPriceCount')
                ->where(['id' => $this->request['id']])
                ->asArray()
                ->one();
    }
    
    public function actionGet()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            Pricelist::find()
                ->where(['id' => $this->request['id']])
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
            
            $item = $this->getPricelistOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('pricelist_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = Pricelist::create();
            $result['log'] = ['data_before' => []];
        }
        
        $item->load($this->request, '');
        
        $transaction = Pricelist::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }
            
            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
        
        if (isset($this->request['old_pricelist_id'])) {
            $item->importFromOldVersion($this->request['old_pricelist_id']);
        }
    
        $result['log']['data_after'] = $this->getDataForLog($item);
    
        return $result;
    }
    
    public function actionSaveAndUpdate()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getPricelistOr404($this->request['id']);
        $item->load($this->request, '');
        
        $transaction = Pricelist::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }
            
            $filterBArray = PricelistFilterB::find()
                ->alias('fb')
                ->select('fb.*')
                ->innerJoin('billing_uu.pricelist_filter_a as fa', 'fa.id = fb.pricelist_filter_a_id')
                ->innerJoin('billing_uu.pricelist_location as pl', 'pl.id = fa.pricelist_location_id')
                ->with('prefixPriceBasic')
                ->where('pl.pricelist_id = :pricelist_id')
                ->addParams([':pricelist_id' => $this->request['id']])
                ->all();
            
            foreach ($filterBArray as $filterB) {
                $filterB->tarification_free_seconds = $item->default_tarification_free_seconds;
                $filterB->tarification_interval_seconds = $item->default_tarification_interval_seconds;
                $filterB->tarification_min_paid_seconds = $item->default_tarification_min_paid_seconds;
                $filterB->tarification_type = $item->default_tarification_type;
                
                $prefixesToSave = [];
                $prefixesToSaveFlat = [];
                
                foreach ($filterB->prefixPriceBasic as $prefixPrice) {
                    if (!isset($prefixesToSave[$prefixPrice->prefix_b])) {
                        $prefixesToSave[$prefixPrice->prefix_b] = ['date_from' => $prefixPrice->date_from, 'id' => $prefixPrice->id];
                    } else {
                        $dateFromCompare = date_create_from_format('Y-m-d', $prefixesToSave[$prefixPrice->prefix_b]['date_from']);
                        $dateFromCurrent = date_create_from_format('Y-m-d', $prefixPrice->date_from);
                        if ($dateFromCurrent > $dateFromCompare) {
                            $prefixesToSave[$prefixPrice->prefix_b] = ['date_from' => $prefixPrice->date_from, 'id' => $prefixPrice->id];
                        }
                    }
                }
                
                foreach ($prefixesToSave as $prefix) {
                    $prefixesToSaveFlat[] = $prefix['id'];
                }
                
                foreach ($filterB->prefixPriceBasic as $prefixPrice) {
                    if (in_array($prefixPrice->id, $prefixesToSaveFlat)) {
                        $prefixPrice->date_from = $item->date_start;
    
                        if (!$prefixPrice->save()) {
                            throw new FormValidationException($item);
                        }
                    } else {
                        $prefixPrice->delete();
                    }
                }
    
                if (!$filterB->save()) {
                    throw new FormValidationException($item);
                }
            }
            
            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }
    
    public function actionToggleActive()
    {
        if (!\Yii::$app->user->can('pricelist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getPricelistOr404($this->request['id']);
        
        if ($item->isInCommercialUse() && $item->is_active) {
            throw new Exception('In commercial use!');
        } else {
            $item->is_active = !$item->is_active;
            $item->save();
        }
        
    }
    
    public function actionInherit()
    {
        if (!\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $name = $this->request['name'];
        
        if (!empty($name)) {
            $result = (new Query())->select(['id' => new Expression('billing_uu.clone_pricelist(:old_pricelist_id, true, :name)')])
                ->addParams([
                    ':old_pricelist_id' => $this->request['id'],
                    ':name' => $name
                ])->one();
        } else {
            $result = (new Query())->select(['id' => new Expression('billing_uu.clone_pricelist(:old_pricelist_id, true)')])
                ->addParams([
                    ':old_pricelist_id' => $this->request['id']
                ])->one();
        }
        
        return $result;
    }
    
    public function actionCopy()
    {
        if (!\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $result = (new Query())->select(['id' => new Expression('billing_uu.clone_pricelist(:old_pricelist_id)')])->addParams([':old_pricelist_id' => $this->request['id']])->one();
        
        return $result;
    }
    
    public function actionCopyAndMultiply()
    {
        if (!\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $result = (new Query())
            ->select(['id' => new Expression('billing_uu.clone_pricelist(:old_pricelist_id, :multiplier)')])
            ->addParams(
                [
                    ':old_pricelist_id' => $this->request['id'],
                    ':multiplier' => $this->request['multiplier']
                ]
            )
            ->one();
        
        return $result;
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
        
        $item = $this->getPricelistOr404($this->request['id']);
        
        try {
            $item->delete();
        } catch (IntegrityException $e) {
            return ['errors' => [['code' => $e->getCode(), 'message' => $e->getMessage()]]];
        }
    }
    
    
    public function actionSearch()
    {
        if (!\Yii::$app->user->can('pricelist_search')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $fields = [
            'a_country_id','b_country_id','a_region_id','b_region_id','a_city_id','b_city_id','a_operator_id',
            'b_operator_id','a_ndc_id','b_ndc_id','timestamp','number_a','number_b','mcc','mnc','location_id',
            'service_type_id'
        ];
        
        $apiUrl = 'http://reg10.mcntelecom.ru:8032/';
        
        $apiParams = [
            'cmd' => 'findPricelist'
        ];
        
        foreach ($fields as $fieldName) {
            if (isset($this->request[$fieldName]) && !empty($this->request[$fieldName])) {
                $apiParams[$fieldName] = $this->request[$fieldName];
            }
        }
    
        if (isset($this->request['is_orig'])) {
            $apiParams['is_orig'] = $this->request['is_orig'];
        }
        
        $request = $apiUrl . 'test/nnpcalc?' . http_build_query($apiParams);
        
        $response = file_get_contents($request);
        
        $response = json_decode($response, true);
        
        $result = self::processSearchResult($response['paths']);
        
        return [
            'params' => $response['params'],
            'paths' => $result,
            'size' => $response['size'],
            'url' => $request
        ];
    }
    
    private function processSearchResult($data)
    {
        $result = [];
        
        foreach ($data as $item) {
            $pricelistId = $item['pricelist_id'];
            
            if (!isset($result[$pricelistId])) {
                $result[$pricelistId] = [];
            }
            
            if (count($result[$pricelistId]) >= self::SEARCH_LIMIT_PER_PRICELIST) {
                if (!isset($result[$pricelistId]['extra_count'])) {
                    $result[$pricelistId]['extra_count'] = 0;
                }
                
                $result[$pricelistId]['extra_count']++;
                continue;
            }
            
            if (count($result[$pricelistId]) == 0) {
                $pricelist = Pricelist::findOne(['id' => $pricelistId]);
                
                if ($pricelist) {
                    $item['pricelist_name'] = $pricelist->name;
                    $item['date_created'] = $pricelist->date_created;
                    $item['date_start'] = $pricelist->date_start;
                }
                
                $result[$item['pricelist_id']][] = $item;
            } else {
                unset($item['pricelist_id']);
                $result[$pricelistId][] = $item;
            }
        }
        
        return $result;
    }
    
    public function actionOldSearch()
    {
        if (!\Yii::$app->user->can('old_pricelist_search')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $fields = [
            'country_code','prefix','pricelist_ids'
        ];
        
        $apiUrl = 'http://reg10.mcntelecom.ru:8032/';
        
        $apiParams = [
            'cmd' => 'findDefs'
        ];
        
        foreach ($fields as $fieldName) {
            if (isset($this->request[$fieldName]) && !empty($this->request[$fieldName])) {
                $apiParams[$fieldName] = $this->request[$fieldName];
            }
        }
        
        if (isset($this->request['exact_match'])) {
            $apiParams['exact_match'] = $this->request['exact_match'];
        }
        
        $request = $apiUrl . 'test/nnpcalc?' . http_build_query($apiParams);
        
        $response = file_get_contents($request);
        
        return json_decode($response, true);
    }
}
