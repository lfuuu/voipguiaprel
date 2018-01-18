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
        $pathName = $this->request['path_name'];
        
        $server = $this->getServerOr404($serverId);
    
        $apiUrl = $server->apiUrl;
        
        $apiParams = [
            'cmd' => 'getAsrAcdSubTree',
            'path' => '[' . $path . ']',
            'max_level' => 3,
        ];
    
        $request = $apiUrl . 'api/asracd?' . http_build_query($apiParams);
        
        $response = file_get_contents($request);
        
        $result = $this->processResult(json_decode($response, true), $path, $pathName);
        
        return $result;
    }
    
    private function processItemRecursive($item, $isTopItem, $path = '', $pathName = '')
    {
        if ($item == "...") {
            return $item;
        }
        
        $newItem = $item;
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
            ->select(new Expression('case when ' . $tableInfo['name'] . ' ~ \'^[а-яА-Я"\s]+$\' then ' . $tableInfo['name'] . ' else ' . $tableInfo['name_fallback'] . ' end as name'))
            ->from($tableInfo['table'])
            ->where($tableInfo['key'] . ' = :id')
            ->limit(1)
            ->addParams([':id' => $newItem['key']])
            ->one();

        $newItem['item_name'] = $name['name'];
        
        if (!$isTopItem) {
            if (!empty($path)) {
                $newItem['path'] = $path . ',' . $newItem['key'];
                $newItem['path_name'] = $pathName . '/[' . $newItem['name'] . ': ' . $newItem['key'] . ': ' . $newItem['item_name'] . ']';
            } else {
                $newItem['path'] = $newItem['key'];
                $newItem['path_name'] = '[' . $newItem['name'] . ': ' . $newItem['key'] . ': ' . $newItem['item_name'] . ']';
            }
        } else {
            $newItem['path'] = $path;
            $newItem['path_name'] = $pathName;
        }

        if (array_key_exists('subitems', $item) && count($item['subitems']) > 0) {
            $hasVisibleSubItems = true;
            
            foreach ($item['subitems'] as $subItemKey => $subItem) {
                $newItem['subitems'][$subItemKey] = $this->processItemRecursive($subItem, false, $newItem['path'], $newItem['path_name']);
                
                if ($newItem['subitems'][$subItemKey] == "...") {
                    $hasVisibleSubItems = false;
                    unset($newItem['subitems'][$subItemKey]);
                }
                
                $newItem['has_visible_subitems'] = $hasVisibleSubItems;
            }
        } else {
            $item['subitems'] = [];
    
            $newItem['has_visible_subitems'] = false;
        }
        
        return $newItem;
    }
    
    private function processResult($result, $path, $pathName)
    {
        if (array_key_exists('err', $result)) {
            throw new Exception('ERROR|Неопознанная ошибка');
        }
        
        $newResult = [];
    
        $newItem['has_visible_subitems'] = true;
        
        foreach ($result['subitems'] as $subItemKey => $subItem) {
            $newResult['subitems'][$subItemKey] = $this->processItemRecursive($subItem, true, $path, $pathName);
        }
        
        return $newResult;
    }
}
