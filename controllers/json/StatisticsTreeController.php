<?php

namespace app\controllers\json;

use app\classes\JsonController;
use yii\base\Exception;
use yii\db\Expression;
use yii\db\Query;

class StatisticsTreeController extends JsonController
{
    private $_levelTables = [
        'hub' => ['table' => 'public.server', 'key' => 'id', 'name' => 'name', 'name_fallback' => 'name'],
        'region' => ['table' => 'public.server', 'key' => 'id', 'name' => 'name', 'name_fallback' => 'name'],
        'trunk' => ['table' => 'auth.trunk', 'key' => 'id', 'name' => 'name', 'name_fallback' => 'name'],
        'nnp_country' => ['table' => 'nnp.country', 'key' => 'code', 'name' => 'name_rus', 'name_fallback' => 'name'],
        'nnp_operator' => ['table' => 'nnp.operator', 'key' => 'id', 'name' => 'name', 'name_fallback' => 'name_translit'],
        'nnp_region' => ['table' => 'nnp.region', 'key' => 'id', 'name' => 'name', 'name_fallback' => 'name_translit'],
        'nnp_city' => ['table' => 'nnp.city', 'key' => 'id', 'name' => 'name', 'name_fallback' => 'name_translit']
    ];
    
    public function actionGet()
    {
        $serverId = $this->request['server_id'];
        $path = $this->request['path'];
        $coreKey = $this->request['core_key'];
        
        $server = $this->getServerOr404($serverId);
    
        $apiUrl = $server->apiUrl;
        
        $apiParams = [
            'cmd' => 'getAsrAcdSubTree',
            'path' => '[' . $path . ']',
            'max_level' => 2,
        ];
    
        $request = $apiUrl . 'api/asracd?' . http_build_query($apiParams);
        
        $response = file_get_contents($request);
        
        list ($result, $coreKey) = $this->processResult(json_decode($response, true), $path, $coreKey);
        
        return ['result' => $result, 'core_key' => $coreKey];
    }
    
    private function processResult($result, $path, $coreKey)
    {
        if (array_key_exists('err', $result)) {
            return [[], ''];
        }
        
        $newResult = [];
        
        $newItem['has_visible_subitems'] = true;
        
        if (empty($coreKey)) {
            foreach ($result['subitems'] as $subitem) {
                $coreKey = $subitem['key'];
                break;
            }
        }
        
        foreach ($result['subitems'] as $subItemKey => $subItem) {
            $newResult['subitems'][$subItemKey] = $this->processItemRecursive($subItem, true, $path);
        }
        
        return array($newResult, $coreKey);
    }
    
    private function processItemRecursive($item, $isTopItem, $path = '')
    {
        if ($item == "...") {
            return $item;
        }
        
        $newItem = $item;
        $newItem['money'] = $this->getMoneyText($item);
        $newItem['asr'] = [];
        $newItem['acd'] = [];
    
        foreach ($item['asr'] as $asrItem) {
            $newItem['asr'][] = number_format($asrItem, 2, '.', '');
        }
    
        foreach ($item['acd'] as $acdItem) {
            $newItem['acd'][] = number_format($acdItem, 2, '.', '');
        }
        
        $newItem['subitems'] = [];
        
        $newItem['is_top_item'] = $isTopItem;
        
        $tableInfo = $this->_levelTables[$newItem['name']];
    
        $name = (new Query())
            ->select(new Expression('case when ' . $tableInfo['name'] . ' ~ \'^[0-9а-яА-ЯёЁ\+\-()№*\/,."\s]+$\' then ' . $tableInfo['name'] . ' else ' . $tableInfo['name_fallback'] . ' end as name'))
            ->from($tableInfo['table'])
            ->where($tableInfo['key'] . ' = :id')
            ->limit(1)
            ->addParams([':id' => $newItem['key']])
            ->one();

        $newItem['item_name'] = $name['name'];
        
        if (!$isTopItem) {
            if (!empty($path)) {
                $newItem['path'] = $path . ',' . $newItem['key'];
            } else {
                $newItem['path'] = $newItem['key'];
            }
        } else {
            $newItem['path'] = $path;
        }
        
        if (array_key_exists('subitems', $item) && count($item['subitems']) > 0) {
            $hasVisibleSubItems = true;
            
            foreach ($item['subitems'] as $subItemKey => $subItem) {
                $newItem['subitems'][$subItemKey] = $this->processItemRecursive($subItem, false, $newItem['path']);
                
                if ($newItem['subitems'][$subItemKey] == "...") {
                    $hasVisibleSubItems = false;
                    unset($newItem['subitems'][$subItemKey]);
                }
                
                $newItem['has_visible_subitems'] = $hasVisibleSubItems;
            }
        } else {
            $newItem['has_visible_subitems'] = false;
        }
        
        return $newItem;
    }

    private function getMoneyText($item)
    {
        $newMoney = [];
        $newMoneyText = "";
    
        foreach ($item['money'] as $money) {
            if (!empty($money)) {
                foreach ($money as $moneyName => $moneyArray) {
                    $newMoney[$moneyName]['cost'][] = $moneyArray['cost'];
                    $newMoney[$moneyName]['rate'][] = $moneyArray['rate'];
                }
            }
        }
    
        foreach ($newMoney as $currency => $money) {
            $cost = implode(', ', $money['cost']);
            $rate = implode(', ', $money['rate']);
            $newMoneyText .= $currency . ': cost: [' . $cost . '], rate: [' . $rate . "]\n";
        }
        
        return $newMoneyText;
    }
    
}
