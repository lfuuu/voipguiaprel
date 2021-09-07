<?php

namespace app\classes\traits;

use yii\web\ForbiddenHttpException;

trait TestResult
{
    protected function processResult($result, $isSms = false)
    {
        $newResult = [];

        $iterate = $isSms ? $result[$this->stepParamName] : $result[0][$this->stepParamName];
        foreach ($iterate as $stepKey => $step) {
            $newResult[$this->stepParamName][$stepKey] = $this->processItemRecursive($step, $stepKey);
        }
    
        return $newResult;
    }
    
    protected function processItemRecursive($item, $path = '')
    {
        $newItem = $item;
    
        $newItem['path'] = $path;
    
        if (array_key_exists($this->stepParamName, $item) && count($item[$this->stepParamName]) > 0) {
            foreach ($item[$this->stepParamName] as $stepKey => $step) {
                $newItem[$this->stepParamName][$stepKey] = $this->processItemRecursive($step, $path . ',' . $stepKey);
            }
        }
        
        return $newItem;
    }
    
    protected function findByPath($result, $path, $depth = self::TEST_RESULT_DEFAULT_DEPTH)
    {
        $newResult = [];
        
        if (empty($path)) {
            if (!empty($result) && array_key_exists($this->stepParamName, $result)) {
                foreach ($result[$this->stepParamName] as $stepKey => $step) {
                    $newResult[$this->stepParamName][$stepKey] = $this->findByPathRecursive($step, $depth - 1);
                }
            }
        } else {
            $pathArray = explode(',', $path);
            
            $newResult = &$result;
    
            foreach ($pathArray as $key) {
                $newResult = &$newResult[$this->stepParamName][$key];
            }
            
            if (array_key_exists($this->stepParamName, $newResult)) {
                foreach ($newResult[$this->stepParamName] as &$step) {
                    if (array_key_exists($this->stepParamName, $step)) {
                        $step[$this->stepParamName] = [];
                    }
                }
            }
        }
        
        return $newResult;
    }
    
    protected function findByPathRecursive($item, $depth)
    {
        $newItem = $item;
    
        if ($depth == 0) {
            if (array_key_exists($this->stepParamName, $item)) {
                $newItem[$this->stepParamName] = [];
            }
        } else {
            if (array_key_exists($this->stepParamName, $item) && count($item[$this->stepParamName]) > 0) {
                foreach ($item[$this->stepParamName] as $stepKey => $step) {
                    $newItem[$this->stepParamName][$stepKey] = $this->findByPathRecursive($step, $depth - 1);
                }
            }
        }
    
        return $newItem;
    }
    
    public function actionDescend()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $path = $this->request['path'];
        $key = $this->request['key'];
        
        $data = \Yii::$app->cache->get($key);
        
        if ($data === false) {
            throw new \Exception('No data in cache');
        }
        
        return $this->findByPath($data, $path);
    }
    
    public function actionClearCache()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        \Yii::$app->cache->flush();
        
        return ['success' => 1];
    }
}