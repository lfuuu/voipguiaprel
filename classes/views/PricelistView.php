<?php

namespace app\classes\views;

use app\models\billing_uu\PricelistPrefixPrice;
use app\models\billing_uu\PricelistLocation;
use app\classes\traits\PricelistView as PricelistViewTrait;
use app\models\billing_uu\A2pAlphaNumberListGroup;
use app\models\billing_uu\A2pAlphaNumbers;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class PricelistView
{
    use PricelistViewTrait;
    
    public static function getForFullForm($queryResult)
    {
        $locationsProcessed = [];
        $filtersAProcessed = [];
        $filtersBProcessed = [];
        
        $mccIdArray = [];
        $nnpCountryIdArray = [];
        $nnpDestinationIdArray = [];
        $nnpOperatorIdArray = [];
        $nnpRegionIdArray = [];
        $nnpCityIdArray = [];
        $nnpNdcTypeIdArray = [];
        
        $idArrays = [
            'nnp.mcc' => ['ids' => &$mccIdArray, 'name_field' => 'country', 'id_field' => 'mcc'],
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
        
        // self::sortAlphabetically($queryResult, $idArrays);
        
        foreach ($queryResult as $queryItem) {
            if (empty($result)) {
                $result[$counter] = ['is_pricelist_header' => true];
                $counter++;
                $result[$counter] = ['is_pricelist_header_columns' => true];
                $counter++;
                $result[$counter] = self::createPricelistRowFull($queryItem);
                $counter++;
            }
            
            if (empty($queryItem['pl__id'])) {
                continue;
            }
            
            if (!isset($result[$locationKey]['is_location']) || ($result[$locationKey]['is_location'] && $result[$locationKey]['id'] != $queryItem['pl__id'])) {
                if ($locationKey != ($counter - 1)) {
                    $result[$counter] = ['is_location_header' => true];
                    $counter++;
                    $result[$counter] = ['is_location_header_columns' => true];
                    $counter++;
                }
                
                $result[$counter] = self::createLocationRowFull($queryItem, $idArrays);
                $locationKey = $counter;
                $counter++;
            }
            
            if (empty($queryItem['pfa__id'])) {
                continue;
            }
            
            if (!isset($result[$filterAKey]['is_filter_a']) || ($result[$filterAKey]['is_filter_a'] && $result[$filterAKey]['id'] != $queryItem['pfa__id'])) {
                if ($filterAKey != ($counter - 1)) {
                    $result[$counter] = ['is_filter_a_header' => true, 'fiter_a_header_id' => $queryItem['pfa__id']];
                    $counter++;
                    $result[$counter] = ['is_filter_a_header_columns' => true];
                    $counter++;
                }
                
                $result[$counter] = self::createFilterARowFull($queryItem, $idArrays);
                $filterAKey = $counter;
                $counter++;
            }
            
            if (empty($queryItem['pfb__id'])) {
                continue;
            }
            
            if (!isset($result[$filterBKey]['is_filter_b']) || ($result[$filterBKey]['is_filter_b'] && $result[$filterBKey]['id'] != $queryItem['pfb__id'])) {
                if ($filterBKey != ($counter - 1)) {
                    $result[$counter] = ['is_filter_b_header' => true, 'fiter_b_header_id' => $queryItem['pfb__id']];
                    $counter++;
                    $result[$counter] = ['is_filter_b_header_columns' => true];
                    $counter++;

                }
                
                $result[$counter] = self::createFilterBRowFull($queryItem, $idArrays);
                $filterBKey = $counter;
                $counter++;
            }
            
            if (empty($queryItem['ppp__id'])) {
                continue;
            }
            
            if (!isset($result[$counter - 1]['prefix_price_id']) || $result[$counter - 1]['prefix_price_id'] != $queryItem['ppp__id']) {
                if ($filterBKey == ($counter - 1)) {
                    $result[$counter] = ['is_prefix_price_header' => true];
                    $counter++;
                    $result[$counter] = ['is_prefix_price_header_columns' => true];
                    $counter++;
                }
                
                if (($counter - $filterBKey) < (PricelistPrefixPrice::PAGE_LIMIT + 3)) {
                    $result[$counter] = self::createPrefixRowFull($queryItem);
                    $counter++;
                    if (($counter - $filterBKey) == (PricelistPrefixPrice::PAGE_LIMIT + 3)) {
                        $count = (new Query())
                            ->select('id')
                            ->distinct()
                            ->from('billing_uu.pricelist_prefix_price')
                            ->where('pricelist_filter_b_id = :b_id')
                            ->andWhere('date_to > now()')
                            ->addParams([':b_id' => $queryItem['pfb__id']])
                            ->count();
                    
                        $result[$counter] = self::createPrefixFooterRowFull($queryItem, $result[$filterBKey]['id'], $count);
                        $counter++;
                    }
                }
            }
        }

        return $result;
    }
    
    public static function getForShortForm($queryResult)
{
    // === множества для O(1) ===
    $locationsProcessed = [];
    $filtersAProcessed  = [];
    $filtersBProcessed  = [];

    // === сбор ID для справочников ===
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
        'nnp.ndc_type' => ['ids' => &$nnpNdcTypeIdArray, 'name_field' => 'name', 'id_field' => 'id'],
    ];

    // === алфанумерики (как было) ===
    $alphaNames = A2pAlphaNumbers::find()
        ->alias('a')
        ->select('a.alphanum, ag.group_id')
        ->innerJoin(A2pAlphaNumberListGroup::tableName() . ' ag', 'ag.alphanum_list_id = a.id')
        ->asArray()
        ->all();
    $alphaNames = ArrayHelper::index($alphaNames, ['alphanum'], 'group_id');

    // === первый проход: собираем справочные id ===
    foreach ($queryResult as $row) {
        $plId  = $row['pl__id']  ?? null;
        $pfaId = $row['pfa__id'] ?? null;
        $pfbId = $row['pfb__id'] ?? null;

        if ($plId !== null && !isset($locationsProcessed[$plId])) {
            self::processQueryArray($mccIdArray, $row['pl__mcc']);
            self::processQueryArray($simImsiPartnerIdArray, $row['pl__sim_partner']);
            self::processQueryArray($simImsiProfileIdArray, $row['pl__sim_profile']);
            $locationsProcessed[$plId] = true;
        }
        if ($pfaId !== null && !isset($filtersAProcessed[$pfaId])) {
            self::processQueryArray($nnpCountryIdArray, $row['pfa__nnp_country']);
            self::processQueryArray($nnpDestinationIdArray, $row['pfa__nnp_destination']);
            self::processQueryArray($nnpOperatorIdArray, $row['pfa__nnp_operator']);
            self::processQueryArray($nnpRegionIdArray, $row['pfa__nnp_region']);
            self::processQueryArray($nnpCityIdArray, $row['pfa__nnp_city']);
            self::processQueryArray($nnpNdcTypeIdArray, $row['pfa__nnp_ndc_type']);
            $filtersAProcessed[$pfaId] = true;
        }
        if ($pfbId !== null && !isset($filtersBProcessed[$pfbId])) {
            self::processQueryArray($nnpCountryIdArray, $row['pfb__nnp_country']);
            self::processQueryArray($nnpDestinationIdArray, $row['pfb__nnp_destination']);
            self::processQueryArray($nnpOperatorIdArray, $row['pfb__nnp_operator']);
            self::processQueryArray($nnpRegionIdArray, $row['pfb__nnp_region']);
            self::processQueryArray($nnpCityIdArray, $row['pfb__nnp_city']);
            self::processQueryArray($nnpNdcTypeIdArray, $row['pfb__nnp_ndc_type']);
            $filtersBProcessed[$pfbId] = true;
        }
    }

    // === батчи справочников ===
    foreach ($idArrays as $key => &$item) {
        $item['ids'] = array_values(array_unique($item['ids']));
        if (!empty($item['ids'])) {
            $temp = (new Query())
                ->select(['id' => $item['id_field'], 'name' => $item['name_field']])
                ->from($key)
                ->where([$item['id_field'] => $item['ids']])
                ->all();
            $map = [];
            foreach ($temp as $t) {
                $map[$t['id']] = $t['name'];
            }
            $item['ids'] = $map;
        }
    }
    unset($item);

    // === кэш счётчиков по filter_b ===
    $pfbCounters = []; // pfb_id => ['real'=>int, 'display'=>int, 'pages'=>int]

    $calcCounters = static function(array $row) use (&$pfbCounters) {
        $pfbId = $row['pfb__id'] ?? null;
        if ($pfbId === null) return ['real'=>0,'display'=>0,'pages'=>0];

        if (isset($pfbCounters[$pfbId])) return $pfbCounters[$pfbId];

        // 1) если в выборке уже есть аггрегаты из LATERAL — используем их
        $real  = isset($row['pfb__total_prefix_count'])     ? (int)$row['pfb__total_prefix_count']     : null;
        $pages = isset($row['pfb__total_pagination_count']) ? (int)$row['pfb__total_pagination_count'] : null;

        if ($real === null) {
            // 2) иначе — один SQL на pfb_id
            $real = (int)(new Query())
                ->select(new Expression("CASE WHEN prefix_b IS NOT NULL AND prefix_b <> '' THEN prefix_b ELSE '' END"))
                ->distinct()
                ->from('billing_uu.pricelist_prefix_price')
                ->where('pricelist_filter_b_id = :b', [':b' => $pfbId])
                ->andWhere('date_to > now()')
                ->count();
        }
        if ($pages === null) {
            $lim = (int)PricelistPrefixPrice::PAGE_LIMIT;
            $pages = ($lim > 0) ? (int)ceil($real / $lim) : 0;
        }

        $display = ($real >= (int)PricelistPrefixPrice::PAGE_LIMIT)
            ? (int)PricelistPrefixPrice::PAGE_LIMIT + 1
            : $real;

        return $pfbCounters[$pfbId] = ['real'=>$real, 'display'=>$display, 'pages'=>$pages];
    };

    // === сбор результата ===
    $result = [];
    $counter = 0;
    $locationKey = 0;
    $filterAKey = 0;
    $filterBKey = 0;

    // карта для быстрого поиска строки по prefix_b в текущем блоке B
    $prefixIndexMap = [];

    self::sortAlphabetically($queryResult, $idArrays);

    foreach ($queryResult as $row) {
        if (empty($result)) {
            $result[0] = self::createPricelistRow($row);
            $counter++;
        }

        if (empty($row['pl__id'])) {
            continue;
        }
        if (!isset($result[$locationKey]['is_location']) ||
            ($result[$locationKey]['is_location'] && $result[$locationKey]['id'] != $row['pl__id'])) {
            $result[$counter] = self::createLocationRow($row, $idArrays);
            $locationKey = $counter;
            $counter++;
        }

        if (empty($row['pfa__id'])) {
            continue;
        }
        if (!isset($result[$filterAKey]['is_filter_a_header']) ||
            ($result[$filterAKey]['is_filter_a_header'] && $result[$filterAKey]['filter_a_id'] != $row['pfa__id'])) {

            $cnt = $calcCounters($row); // ['real','display','pages']
            if ($cnt['display'] > 0) {
                // ВАЖНО: сюда передаём и display, и real — чтобы формат остался прежним
                $result[$counter] = self::createFilterAFilterBPrefixRow($row, $idArrays, $cnt['display'], $cnt['real'], $alphaNames);
                $filterAKey = $counter;
                $filterBKey = $counter;
                $counter++;

                $prefixIndexMap = []; // новый блок B
            }
        }

        if (empty($row['pfb__id'])) {
            continue;
        }
        if (!isset($result[$filterBKey]['is_filter_b_header']) ||
            ($result[$filterBKey]['is_filter_b_header'] && $result[$filterBKey]['filter_b_id'] != $row['pfb__id'])) {

            $cnt = $calcCounters($row);
            if ($cnt['display'] > 0) {
                $result[$counter] = self::createFilterBPrefixRow($row, $idArrays, $cnt['display'], $cnt['real']);
                $filterBKey = $counter;
                $counter++;

                // как в исходнике: суммируем в заголовок A отображаемое число (display), а не real
                if (isset($result[$filterAKey]['total_prefix_count'])) {
                    $result[$filterAKey]['total_prefix_count'] += $cnt['display'];
                }

                $prefixIndexMap = []; // новый блок B
            }
        }

        if (empty($row['ppp__id'])) {
            continue;
        }

        $currentPrefix = ($row['ppp__prefix_b'] ?? '') . ' ';

        // как и было — защита от дубля
        if (($result[$counter - 1]['prefixes'][0]['prefix_price_id'] ?? null) == $row['ppp__id']) {
            continue;
        }

        if (isset($prefixIndexMap[$currentPrefix])) {
            $idx = $prefixIndexMap[$currentPrefix];
            $result[$idx]['prefixes'][] = self::createPrefixItem($row, $result[$idx]['prefixes']);
            // порядок уже отсортирован SQL’ем; динамику пересчитываем
            self::recalcPrefixesDynamics($result[$idx]['prefixes']);
        } else {
            // создаём новую строку префикса, пока не достигли лимита
            $rowsUnderB = $counter - $filterBKey; // как в исходнике
            if ($rowsUnderB < PricelistPrefixPrice::PAGE_LIMIT) {
                $result[$counter] = self::createPrefixRow($row);
                $prefixIndexMap[$currentPrefix] = $counter;
                $counter++;

                if (($counter - $filterBKey) == PricelistPrefixPrice::PAGE_LIMIT) {
                    // добиваем футер реальным количеством
                    $cnt = $calcCounters($row);
                    $result[$counter] = self::createPrefixFooterRow($row, $result[$filterBKey]['filter_b_id'], $cnt['real']);
                    $counter++;
                }
            }
        }
    }

    return $result;
}


    
    private static function sortPrefixes($a, $b)
    {
        return $a['date_from'] > $b['date_from'];
    }
    
    private static function recalcPrefixesDynamics(&$prefixes)
    {
        for ($i = 0; $i < count($prefixes); $i++) {
            if ($i == 0) {
                $prefixes[$i]['price_change'] = 'none';
                continue;
            }
            
            if ($prefixes[$i - 1]['b_number_price'] > $prefixes[$i]['b_number_price']) {
                $prefixes[$i]['price_change'] = 'decrease';
            } elseif ($prefixes[$i - 1]['b_number_price'] < $prefixes[$i]['b_number_price']) {
                $prefixes[$i]['price_change'] = 'increase';
            } else {
                $prefixes[$i]['price_change'] = 'none';
            }
        }
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
    
    private static function createPricelistRowFull($item)
    {
        return [
            'currency_id' => $item['p__currency_id'],
            'date_end' => $item['p__date_end'],
            'date_start' => $item['p__date_start'],
            'id' => $item['p__id'],
            'is_active' => $item['p__is_active'] ? 'Да' : 'Нет',
            'is_global' => $item['p__is_global'] ? 'Да' : 'Нет',
            'is_pricelist' => true,
            'minimum_margin' => $item['p__minimum_margin'],
            'minimum_margin_type' => (($item['p__minimum_margin_type'] == 1) ? 'Деньги' : 'Процент'),
            'name' => $item['p__name'],
            'orig' => $item['p__orig'] ? 'Оригинация' : 'Терминация',
            'version' => $item['p__pricelist_version'],
            'default_tarification_free_seconds' => $item['p__default_tarification_free_seconds'],
            'default_tarification_interval_seconds' => $item['p__default_tarification_interval_seconds'],
            'default_tarification_min_paid_seconds' => $item['p__default_tarification_min_paid_seconds'],
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
    
    private static function createLocationRowFull($item, $idArrays)
    {
        $mcc = self::getNameFromDictionary($item['pl__mcc'], $idArrays['nnp.mcc']['ids']);
        $mnc = self::formMncText($item);
        
        return [
            'delta_price' => $item['pl__delta_price'],
            'has_children' => isset($item['pfa__id']),
            'id' => $item['pl__id'],
            'is_location' => true,
            'location_id' => PricelistLocation::LOCATION_TYPE_NAMES[$item['pl__location_id']],
            'mcc' => $mcc,
            'mnc' => $mnc,
            'parent_id' => $item['p__id'],
            'pricelist_service_type_id' => $item['p__service_type_id'],
            'rounding_treshold' => $item['pl__rounding_threshold']
        ];
    }
    
    private static function createFilterAFilterBPrefixRow($item, $idArrays, $count, $realCount, $alphaNames)
    {
        return [
            'is_filter_b_header' => true,
            'is_filter_a_header' => true,
            'has_filter_a_mark' => false,
            'has_filter_b_mark' => false,
            'filter_a_name' => self::formFilterText($item, 'pfa__', $idArrays, $alphaNames),
            'filter_b_name' => self::formFilterText($item, 'pfb__', $idArrays),
            'filter_b_rating' => (($item['pfb__rating'] == 1) ? '' : $item['pfb__rating']),
            'filter_b_use_for_minimum' => $item['pfb__use_for_minimum'],
            'object_comment' => $item['pfb__object_comment'],
            'filter_a_id' => $item['pfa__id'],
            'filter_b_id' => $item['pfb__id'],
            'is_prefix_price' => true,
            'prefix_b' => $item['ppp__prefix_b'] . ' ',
            'prefix_count' => $count,
            'total_prefix_count' => $count,
            'total_pagination_count' => $realCount,
            'interconnect_price' => empty($item['pfb__interconnect_price']) ? 0 : floatval($item['pfb__interconnect_price']),
            'prefixes' => [
                [
                    'prefix_price_id' => $item['ppp__id'],
                    'has_prefix_mark' => false,
                    'b_number_price' => $item['ppp__b_number_price'],
                    'date_from' => $item['ppp__date_from'],
                    'date_to' => $item['ppp__date_to'],
                    'price_change' => 'none',
                ]
            ]
        ];
    }
    
    private static function createFilterARowFull($item, $idArrays)
    {
        $prefix = 'pfa__';
        
        $countryName = self::getNameFromDictionary($item[$prefix . 'nnp_country'], $idArrays['nnp.country']['ids']);
        $ndcTypeName = self::getNameFromDictionary($item[$prefix . 'nnp_ndc_type'], $idArrays['nnp.ndc_type']['ids']);
        $operatorName = self::getNameFromDictionary($item[$prefix . 'nnp_operator'], $idArrays['nnp.operator']['ids']);
        $regionName = self::getNameFromDictionary($item[$prefix . 'nnp_region'], $idArrays['nnp.region']['ids']);
        $cityName = self::getNameFromDictionary($item[$prefix . 'nnp_city'], $idArrays['nnp.city']['ids']);
        $destinationName = self::getNameFromDictionary($item[$prefix . 'nnp_destination'], $idArrays['nnp.destination']['ids']);
        
        return [
            'has_children' => isset($item['pfb__id']),
            'id' => $item['pfa__id'],
            'is_filter_a' => true,
            'mode_selected' => $item['pfa__mode_selected'] ? 'Выбранные' : 'Кроме выбранных',
            'nnp_city' => ($item[$prefix . 'f_inv_nnp_city'] ? 'Кроме: ' . $cityName : $cityName),
            'nnp_country' => ($item[$prefix . 'f_inv_nnp_country'] ? 'Кроме: ' . $countryName : $countryName),
            'nnp_destination' => ($item[$prefix . 'f_inv_nnp_destination'] ? 'Кроме: ' . $destinationName : $destinationName),
            'nnp_ndc' => ($item[$prefix . 'f_inv_nnp_ndc'] ? 'Кроме: ' . $item[$prefix . 'nnp_ndc'] : $item[$prefix . 'nnp_ndc']),
            'nnp_ndc_type' => ($item[$prefix . 'f_inv_nnp_ndc_type'] ? 'Кроме: ' . $ndcTypeName : $ndcTypeName),
            'nnp_operator' => ($item[$prefix . 'f_inv_nnp_operator'] ? 'Кроме: ' . $operatorName : $operatorName),
            'nnp_region' => ($item[$prefix . 'f_inv_nnp_region'] ? 'Кроме: ' . $regionName : $regionName),
            'parent_id' => $item['pl__id'],
            'regexp' => $item['pfa__regex'],
            'rn_replacement_probability' => $item['pfa__rn_replacement_probability'],
            'time_end' => $item['pfa__time_end'],
            'time_start' => $item['pfa__time_start']
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
            'object_comment' => $item['pfb__object_comment'],
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
                    'price_change' => 'none',
                ]
            ]
        ];
    }
    
    private static function createFilterBRowFull($item, $idArrays)
    {
        $prefix = 'pfb__';
        
        $countryName = self::getNameFromDictionary($item[$prefix . 'nnp_country'], $idArrays['nnp.country']['ids']);
        $ndcTypeName = self::getNameFromDictionary($item[$prefix . 'nnp_ndc_type'], $idArrays['nnp.ndc_type']['ids']);
        $operatorName = self::getNameFromDictionary($item[$prefix . 'nnp_operator'], $idArrays['nnp.operator']['ids']);
        $regionName = self::getNameFromDictionary($item[$prefix . 'nnp_region'], $idArrays['nnp.region']['ids']);
        $cityName = self::getNameFromDictionary($item[$prefix . 'nnp_city'], $idArrays['nnp.city']['ids']);
        $destinationName = self::getNameFromDictionary($item[$prefix . 'nnp_destination'], $idArrays['nnp.destination']['ids']);
        
        return [
            'has_children' => isset($item['ppp__id']),
            'id' => $item['pfb__id'],
            'interconnect_price' => $item['pfb__interconnect_price'],
            'is_filter_b' => true,
            'mode_selected' => $item['pfb__mode_selected'] ? 'Выбранные' : 'Кроме выбранных',
            'nnp_city' => ($item[$prefix . 'f_inv_nnp_city'] ? 'Кроме: ' . $cityName : $cityName),
            'nnp_country' => ($item[$prefix . 'f_inv_nnp_country'] ? 'Кроме: ' . $countryName : $countryName),
            'nnp_destination' => ($item[$prefix . 'f_inv_nnp_destination'] ? 'Кроме: ' . $destinationName : $destinationName),
            'nnp_ndc' => ($item[$prefix . 'f_inv_nnp_ndc'] ? 'Кроме: ' . $item[$prefix . 'nnp_ndc'] : $item[$prefix . 'nnp_ndc']),
            'nnp_ndc_type' => ($item[$prefix . 'f_inv_nnp_ndc_type'] ? 'Кроме: ' . $ndcTypeName : $ndcTypeName),
            'nnp_operator' => ($item[$prefix . 'f_inv_nnp_operator'] ? 'Кроме: ' . $operatorName : $operatorName),
            'nnp_region' => ($item[$prefix . 'f_inv_nnp_region'] ? 'Кроме: ' . $regionName : $regionName),
            'parent_id' => $item['pfa__id'],
            'ported_num_price' => $item['pfb__ported_num_price'],
            'regexp' => $item['pfb__regex'],
            'tarification_free_seconds' => $item['pfb__tarification_free_seconds'],
            'tarification_interval_seconds' => $item['pfb__tarification_interval_seconds'],
            'tarification_min_paid_seconds' => $item['pfb__tarification_min_paid_seconds'],
            'tarification_type' => $item['pfb__tarification_type'],
            'time_end' => $item['pfb__time_end'],
            'time_start' => $item['pfb__time_start'],
            'use_for_minimum' => $item['pfb__use_for_minimum'] ? 'Да' : 'Нет'
        ];
    }
    
    private static function createPrefixRow($item)
    {
        return [
            'is_filter_b_header' => false,
            'is_filter_a_header' => false,
            'is_prefix_price' => true,
            'filter_b_id' => $item['pfb__id'],
            'prefix_b' => $item['ppp__prefix_b'] . ' ',
            'prefixes' => [
                [
                    'prefix_price_id' => $item['ppp__id'],
                    'has_prefix_mark' => false,
                    'b_number_price' => $item['ppp__b_number_price'],
                    'date_from' => $item['ppp__date_from'],
                    'date_to' => $item['ppp__date_to'],
                    'price_change' => 'none',
                ]
            ]
        ];
    }
    
    private static function createPrefixRowFull($item)
    {
        return [
            'is_prefix_price' => true,
            'filter_b_id' => $item['pfb__id'],
            'prefix_price_id' => $item['ppp__id'],
            'b_number_price' => $item['ppp__b_number_price'],
            'change_flag' => $item['ppp__change_flag'],
            'prefix_b' => $item['ppp__prefix_b'],
            'date_from' => $item['ppp__date_from'],
            'date_to' => $item['ppp__date_to'],
            'has_buttons' => true,
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
            'price_change' => ($previousPrice > $item['ppp__b_number_price'] ? 'decrease' : ($previousPrice == $item['ppp__b_number_price'] ? 'none' : 'increase')),
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
    
    private static function createPrefixFooterRowFull($item, $filterBId, $count)
    {
        return [
            'is_prefix_price_footer' => true,
            'totalCount' => $count,
            'currentPage' => 1,
            'offset' => 0,
            'filter_b_id' => $filterBId
        ];
    }
}
