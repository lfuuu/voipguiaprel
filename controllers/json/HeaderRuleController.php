<?php

namespace app\controllers\json;

use app\models\auth\HeaderRuleItem;
use Yii;
use app\classes\JsonController;
use app\models\auth\HeaderRule;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class HeaderRuleController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('header_rule_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        return
            HeaderRule::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead()
    {
        if (!\Yii::$app->user->can('header_rule_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $server = $this->getServerOr404($this->request['server_id']);
    
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;
        
        return
            HeaderRule::find()
                ->alias('h')
                ->select(['id', 'name', 'description', 'sw_shared', 'object_comment'])
                ->orderBy('name')
                ->where("(h.server_id in (select id from public.server where hub_id = :hub_id) and sw_shared) or h.server_id = :server_id")
                ->addParams([':hub_id' => $hub_id, ':server_id' => $server->id])
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('header_rule_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $item = HeaderRule::find()
            ->with('items')
            ->where(['id' => $this->request['id']])
            ->asArray()
            ->one();
    
        if ($item === null) {
            throw new HttpException(404, 'Header Rule не найден');
        }
    
        return $item;
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('header_rule_edit') && !\Yii::$app->user->can('header_rule_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $result = [];
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('header_rule_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getHeaderRuleOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('header_rule_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = HeaderRule::create();
            $result['log'] = ['data_before' => []];
        }

        $item->load($this->request, '');

        $transaction = HeaderRule::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }
    
            HeaderRuleItem::deleteByHeaderRule($item);
            $order = 1;
            foreach ($this->request['items'] as $itemData) {
                $ruleItem = HeaderRuleItem::create($item, $itemData);
                $ruleItem->order = $order;
                if (!$ruleItem->save()) {
                    throw new FormValidationException($ruleItem);
                }
                $order++;
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    
        $result['log']['data_after'] = $this->getDataForLog($item);
    
        return $result;
    }

    public function actionDelete()
    {
        if (!\Yii::$app->user->can('header_rule_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = HeaderRule::findOne($this->request['id']);
        $item->delete();
    }
}
