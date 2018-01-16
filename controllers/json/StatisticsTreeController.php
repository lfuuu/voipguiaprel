<?php

namespace app\controllers\json;

use app\classes\JsonController;
use yii\base\Exception;
use yii\db\Query;

class StatisticsTreeController extends JsonController
{
    private $_levelTables = [
        'hub' => ['table' => 'public.server', 'key' => 'id'],
        'region' => ['table' => 'public.server', 'key' => 'id'],
        'trunk' => ['table' => 'auth.trunk', 'key' => 'id'],
        'nnp_country' => ['table' => 'nnp.country', 'key' => 'code'],
        'nnp_operator' => ['table' => 'nnp.operator', 'key' => 'id'],
        'nnp_region' => ['table' => 'nnp.region', 'key' => 'id'],
        'nnp_city' => ['table' => 'nnp.city', 'key' => 'id']
    ];
    
    public function actionGet()
    {
        $serverId = $this->request['server_id'];
        $path = $this->request['path'];
        
        $server = $this->getServerOr404($serverId);
    
        $apiUrl = $server->apiUrl;
        
        $apiParams = [
            'cmd' => 'getAsrAcdSubTree',
            'path' => '[' . $path . ']',
            'max_level' => 3,
        ];
    
        $request = $apiUrl . 'api/asracd?' . http_build_query($apiParams);
        
        $response = file_get_contents($request);
        
        $result = $this->processResult(json_decode($response, true), $path);
        
        return $result;
    }
    
    private function processItemRecursive($item, $isTopItem, $path = '')
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
        
        if (!$isTopItem) {
            if (!empty($path)) {
                $newItem['path'] = $path . ',' . $newItem['key'];
            } else {
                $newItem['path'] = $newItem['key'];
            }
        } else {
            $newItem['path'] = $path;
        }
        
        $tableInfo = $this->_levelTables[$newItem['name']];

        $name = (new Query())
            ->select('name')
            ->from($tableInfo['table'])
            ->where($tableInfo['key'] . ' = :id')
            ->limit(1)
            ->addParams([':id' => $newItem['key']])
            ->one();

        $newItem['item_name'] = $name['name'];
        
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
            $item['subitems'] = [];
    
            $newItem['has_visible_subitems'] = false;
        }
        
        return $newItem;
    }
    
    private function processResult($result, $path)
    {
        if (array_key_exists('err', $result)) {
            throw new Exception('ERROR|Неопознанная ошибка');
        }
        
        $newResult = [];
    
        $newItem['has_visible_subitems'] = true;
        
        foreach ($result['subitems'] as $subItemKey => $subItem) {
            $newResult['subitems'][$subItemKey] = $this->processItemRecursive($subItem, true, $path);
        }
        
        return $newResult;
    }
}
