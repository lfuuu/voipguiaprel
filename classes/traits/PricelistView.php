<?php

namespace app\classes\traits;

use app\models\billing_uu\PricelistPrefixPrice;
use app\models\billing_uu\PricelistLocation;
use yii\db\Query;

trait PricelistView
{
    public static function processQueryArray(&$idArray, $queryItemElement)
    {
        if (empty($queryItemElement) || $queryItemElement == '{}') {
            return;
        }

        $cleanedElement = str_replace(['{', '}'], '', $queryItemElement);
        $explodedElement = explode(',', $cleanedElement);
        $idArray = array_merge($idArray, $explodedElement);
    }
    
    public static function formLocationText($item, $isBasic, $idArrays, $prefix = 'pl__')
    {
        $locationText = '';
        
        $mcc = self::getNameFromDictionary($item[$prefix. 'mcc'], $idArrays['nnp.mcc']['ids']);
        $simPartner = self::getNameFromDictionary($item[$prefix. 'sim_partner'], $idArrays['billing_uu.sim_imsi_partner']['ids']);
        $simProfile = self::getNameFromDictionary($item[$prefix. 'sim_profile'], $idArrays['billing_uu.sim_imsi_profile']['ids']);
        $mnc = self::formMncText($item);
        
        $locationText = !empty($item[$prefix. 'description']) ? $item[$prefix. 'description'] : 
            ((($isBasic ? 'Базовое местоположение: ' : 'Местоположение: ') . PricelistLocation::LOCATION_TYPE_NAMES[$item[$prefix. 'location_id']]) . 
            (empty($mcc) ? '' : ('; MCC: ' . $mcc)) . (empty($mnc) ? '' : ('; MNC: ' . $mnc)) . 
            (empty($item[$prefix. 'delta_price']) ? '' : '; Наценка: ' . $item[$prefix. 'delta_price']) . 
            (empty($simPartner) ? '' : ('; Sim Партнер: ' . $simPartner)) . (empty($simProfile) ? '' : ('; Sim Профиль: ' . $simProfile)));
        
        return $locationText;
    }
    
    public static function formMncText($item, $prefix = 'pl__')
    {
        if (!empty($item[$prefix. 'mcc']) && $item[$prefix. 'mcc'] != '{}' && !empty($item[$prefix. 'mnc'] && $item[$prefix. 'mnc'] != '{}')) {
            $tempMcc = str_replace(['{', '}'], '', $item[$prefix. 'mcc']);
            $tempMcc = explode(',', $tempMcc);
            $tempMnc = str_replace(['{', '}'], '', $item[$prefix. 'mnc']);
            $tempMnc = explode(',', $tempMnc);
            $mncArray = (new Query())->select('network')->from('nnp.mnc')->where(['mcc' => $tempMcc, 'mnc' => $tempMnc])->all();
            $mncFormattedArray = [];
            
            foreach ($mncArray as $mncItem) {
                $mncFormattedArray[] = $mncItem['network'];
            }
            
            $mnc = implode(', ', $mncFormattedArray);
        } else {
            $mnc = '';
        }
        
        return $mnc;
    }
    
    public static function formFilterText($item, $prefix, $idArrays)
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
    
    public static function getNameFromDictionary($idString, $dictionary)
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
    
    public static function sortAlphabetically(&$arrayToSort, $idArrays)
    {
        usort($arrayToSort, function($a, $b) use ($idArrays) {
            if ($a['pl__id'] == $b['pl__id']) {
                if ($a['pfa__id'] == $b['pfa__id']) {
                    if ($a['pfb__id'] == $b['pfb__id']) {
                        return strcmp($a['ppp__prefix_b'] . ' ', $b['ppp__prefix_b'] . ' ');
                    } else {
                        $filterBTextA = self::formFilterText($a, 'pfb__', $idArrays);
                        $filterBTextB = self::formFilterText($b, 'pfb__', $idArrays);
                        
                        return strcmp($filterBTextA, $filterBTextB);
                    }
                } else {
                    $filterATextA = self::formFilterText($a, 'pfa__', $idArrays);
                    $filterATextB = self::formFilterText($b, 'pfa__', $idArrays);
                    
                    return strcmp($filterATextA, $filterATextB);
                }
            } else {
                $isBasicA = ($a['pl__id'] == $a['p__basic_pricelist_location_id']);
                $isBasicB = ($b['pl__id'] == $b['p__basic_pricelist_location_id']);
                
                $locationTextA = self::formLocationText($a, $isBasicA, $idArrays);
                $locationTextB = self::formLocationText($b, $isBasicB, $idArrays);
                
                return strcmp($locationTextA, $locationTextB);
            }
        });
    }
}