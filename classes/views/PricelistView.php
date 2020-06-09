<?php

namespace app\classes\views;

use app\models\billing_uu\PricelistPrefixPrice;
use app\models\billing_uu\PricelistLocation;
use yii\db\Query;

class PricelistView
{
    public static function getForShortForm($queryResult)
    {
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
                
                if ($count > 0) {
                    $result[$counter] = self::createFilterAFilterBPrefixRow($queryItem, $idArrays, $count, $realCount);
                    $filterAKey = $counter;
                    $filterBKey = $counter;
                    $counter++;
                }
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
                
                if ($count > 0) {
                    $result[$counter] = self::createFilterBPrefixRow($queryItem, $idArrays, $count, $realCount);
                    $filterBKey = $counter;
                    $counter++;
                    $result[$filterAKey]['total_prefix_count'] += $count;
                }
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
                    if (($counter - $filterBKey) == PricelistPrefixPrice::PAGE_LIMIT) {
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
}