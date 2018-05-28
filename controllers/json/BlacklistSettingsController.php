<?php

namespace app\controllers\json;

use app\models\Prefixlist;
use app\models\PrefixlistPrefix;
use app\classes\JsonController;
use yii\web\ForbiddenHttpException;

class BlacklistSettingsController extends JsonController
{
    const RESULT_NOT_EXISTS = 0;
    const RESULT_SUCCESS = 1;
    const RESULT_ALREADY_EXISTS = 2;
    const RESULT_DELETE_SUCCESS = 3;
    const RESULT_LENGTH_TOO_SHORT = 4;
    
    const PREFIX_MIN_LENGTH = 6;
    
    public function actionGet()
    {
        if (!\Yii::$app->user->can('blacklist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);
        
        if (!empty($server->fsb_blacklist_id)) {
            $blacklistSettings = $this->getBlacklistSettingsOr404($server->fsb_blacklist_id);
            $a = [
                'id' => $blacklistSettings->id,
                'server_id' => $server->id,
                'name' => $blacklistSettings->name
            ];
        } else {
            $a = [];
        }
    
        if (!empty($server->fsb_b_blacklist_id)) {
            $blacklistSettingsB = $this->getBlacklistSettingsOr404($server->fsb_b_blacklist_id);
            $b = [
                'id' => $blacklistSettingsB->id,
                'server_id' => $server->id,
                'name' => $blacklistSettingsB->name
            ];
        } else {
            $b = [];
        }
        
        return [
            'a' => $a,
            'b' => $b
        ];
    }
    
    public function actionAdd()
    {
        if (!\Yii::$app->user->can('blacklist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $id = $this->request['id'];
        $prefixesText = $this->request['prefixes'];
        $sharedPrefix = $this->request['shared_prefix'];
        
        $data = $this->getData($id, $prefixesText, $sharedPrefix);
        
        $transaction = Prefixlist::getDb()->beginTransaction();
        try {
            $result = [];
            
            if (count($data) > 0) {
                foreach ($data as $item) {
                    if (strlen($item['prefix']) < self::PREFIX_MIN_LENGTH) {
                        $result[$item['prefix']] = self::RESULT_LENGTH_TOO_SHORT;
                    } else if (!PrefixlistPrefix::find()->where($item)->exists()) {
                        PrefixlistPrefix::getDb()->createCommand()->insert(
                            PrefixlistPrefix::tableName(),
                            $item
                        )->execute();
                        $result[$item['prefix']] = self::RESULT_SUCCESS;
                    } else {
                        $result[$item['prefix']] = self::RESULT_ALREADY_EXISTS;
                    }
                }
            }
        
            $transaction->commit();
            
            $this->updatePrefixlistCount($id);
        
            return ['data'=>['result' => $result]];
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }
    
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('blacklist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $id = $this->request['id'];
        $prefixesText = $this->request['prefixes'];
        $sharedPrefix = $this->request['shared_prefix'];
    
        $data = $this->getData($id, $prefixesText, $sharedPrefix);
        
        $transaction = Prefixlist::getDb()->beginTransaction();
        try {
            $result = [];
            
            if (count($data) > 0) {
                foreach ($data as $item) {
                    if (PrefixlistPrefix::find()->where($item)->exists()) {
                        PrefixlistPrefix::getDb()->createCommand()->delete(
                            PrefixlistPrefix::tableName(),
                            'prefixlist_id = :prefixlist_id and prefix = :prefix',
                            [':prefixlist_id' => $item['prefixlist_id'], ':prefix' => $item['prefix']]
                        )->execute();
                        $result[$item['prefix']] = self::RESULT_DELETE_SUCCESS;
                    } else {
                        $result[$item['prefix']] = self::RESULT_NOT_EXISTS;
                    }
                }
            }
            
            $transaction->commit();
    
            $this->updatePrefixlistCount($id);
            
            return ['data'=>['result' => $result]];
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }
    
    public function actionCheck()
    {
        if (!\Yii::$app->user->can('blacklist_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $id = $this->request['id'];
        $prefixesText = $this->request['prefixes'];
        $sharedPrefix = $this->request['shared_prefix'];
        
        $data = $this->getData($id, $prefixesText, $sharedPrefix);
        
        $result = [];
        
        if (count($data) > 0) {
            foreach ($data as $item) {
                if (PrefixlistPrefix::find()->where($item)->exists()) {
                    $result[$item['prefix']] = self::RESULT_ALREADY_EXISTS;
                } else {
                    $result[$item['prefix']] = self::RESULT_NOT_EXISTS;
                }
            }
        }
        
        return ['data'=>['result' => $result]];
    }
    
    private function getData($id, $prefixesText, $sharedPrefix)
    {
        $del = array(' ', ',', ';', '.', "\n");
    
        $prefixes = explode($del[0], str_replace($del, $del[0], $prefixesText));
    
        $data = [];
    
        if ($sharedPrefix) {
            foreach ($prefixes as $prefix) {
                if ($prefix) {
                    $data[] = ['prefixlist_id' => $id, 'prefix' => $sharedPrefix . $prefix];
                }
            }
        } else {
            foreach ($prefixes as $prefix) {
                if ($prefix) {
                    $data[] = ['prefixlist_id' => $id, 'prefix' => $prefix];
                }
            }
        }
        
        return $data;
    }
    
    private function updatePrefixlistCount($id)
    {
        $prefixlist = Prefixlist::findOne($id);
        $prefixlist->count = PrefixlistPrefix::find()->where(['prefixlist_id' => $id])->count();
        $prefixlist->save();
    }
}
