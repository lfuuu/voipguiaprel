<?php

namespace app\controllers\json;

use app\classes\JsonController;
use yii\db\Query;

class MoneyTreeController extends JsonController
{
    private $_levelTables = [
        'filter_a' => ['table' => 'billing_uu.major', 'key' => 'id', 'name' => 'name'],
        'filter_b' => ['table' => 'billing_uu.major', 'key' => 'id', 'name' => 'name'],
        'trunk' => ['table' => 'auth.trunk', 'key' => 'id', 'name' => 'trunk_name'],
        'operator' => ['table' => 'nnp.operator', 'key' => 'id', 'name' => 'name'],
    ];
    
    public function actionGet()
    {
        $serverId = $this->request['server_id'];
        $path = $this->request['path'];
        $coreKey = $this->request['core_key'];
        
        $server = $this->getServerOr404($serverId);
    
        $apiUrl = $server->apiUrl;
        
        $apiParams = [
            'api' => 1,
            'path' => $path,
            'max_level' => 1,
        ];
        
        $request = $apiUrl . 'moneyTree?' . http_build_query($apiParams);
        
        $response = file_get_contents($request);
        
        list($result, $coreKey) = $this->processResult(json_decode($response, true), $path, $coreKey);
        
        return ['result' => $result, 'core_key' => $coreKey];
    }
    
    private function processResult($result, $path, $coreKey)
    {
        if (array_key_exists('err', $result)) {
            return [[], ''];
        }
        
        $newResult = $result;
        
        if (isset($result['sub'])) {
            if (!isset($coreKey)) {
                $coreKey = '0';
            }
            
            if (!array_key_exists('key', $newResult)) {
                $newResult['key'] = 0;
            }
            
            foreach ($result['sub'] as $subItemKey => $subItem) {
                $newResult['sub'][$subItemKey] = $this->processItemRecursive($subItem, $path);
            }
        } else {
            $newResult['sub'] = [];
        }
        
        return array($newResult, $coreKey);
    }
    
    private function processItemRecursive($item, $path = '')
    {
        $newItem = $item;
        
        if ($item['key']) {
            $name = (new Query())
                ->select([$this->_levelTables[$item['name']]['name']])
                ->from($this->_levelTables[$item['name']]['table'])
                ->where($this->_levelTables[$item['name']]['key'] . ' = :id')
                ->addParams([':id' => $item['key']])
                ->one();
            
            if (isset($name[$this->_levelTables[$item['name']]['name']])) {
                $newItem['real_name'] = $name[$this->_levelTables[$item['name']]['name']];
            } else {
                $newItem['real_name'] = 'Наименование не найдено';
            }
        } else {
            $newItem['real_name'] = $item['key'];
        }
        
        if (isset($path) && $path !== '') {
            $newItem['path'] = $path . ',' . $newItem['key'];
        } else {
            $newItem['path'] = (string)$newItem['key'];
        }
        
        if (array_key_exists('sub', $item) && count($item['sub']) > 0) {
            foreach ($item['sub'] as $subItemKey => $subItem) {
                $newItem['sub'][$subItemKey] = $this->processItemRecursive($subItem, $newItem['path']);
            }
        }
        
        return $newItem;
    }
   
}
