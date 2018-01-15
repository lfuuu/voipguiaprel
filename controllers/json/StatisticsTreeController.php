<?php

namespace app\controllers\json;

use app\classes\JsonController;
use yii\base\Exception;

class StatisticsTreeController extends JsonController
{
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
        
        if (array_key_exists('subitems', $item) && count($item['subitems']) > 0) {
            foreach ($item['subitems'] as $subItemKey => $subItem) {
                $newItem['subitems'][$subItemKey] = $this->processItemRecursive($subItem, false, $newItem['path']);
            }
        } else {
            $item['subitems'] = [];
        }
        
        return $newItem;
    }
    
    private function processResult($result, $path)
    {
        if (array_key_exists('err', $result)) {
            throw new Exception('ERROR|Неопознанная ошибка');
        }
        
        $newResult = [];
        
        foreach ($result['subitems'] as $subItemKey => $subItem) {
            $newResult['subitems'][$subItemKey] = $this->processItemRecursive($subItem, true, $path);
        }
        
        return $newResult;
    }
}
