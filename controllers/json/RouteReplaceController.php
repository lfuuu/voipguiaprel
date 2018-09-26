<?php

namespace app\controllers\json;

use app\models\auth\RouteReplace;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;

class RouteReplaceController extends JsonController
{
    public function actionRead()
    {
        if (!\Yii::$app->user->can('route_replace_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);

        return
            RouteReplace::find()
                ->select([
                    '*',
                    new Expression('case when orig_trunk_group_id is not null then true else false end as is_orig_group'),
                    new Expression('case when term_trunk_group_id is not null then true else false end as is_term_group')
                    ])
                ->where(['server_id' => $server->id])
                ->orderBy('order')
                ->asArray()
                ->all();
    }
    
    public function actionSaveMultiple()
    {
        if (!\Yii::$app->user->can('route_replace_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $order = 1;
        $idsToSave = [];
        $serverId = $this->request['server_id'];
        
        foreach ($this->request['items'] as $requestItem) {
            $requestItem['order'] = $order;
    
            $server = $this->getServerOr404($serverId);
    
            if (isset($requestItem['id'])) {
                $item = $this->getRouteReplaceOr404($requestItem['id']);
            } else {
                $item = RouteReplace::create($server);
            }
    
            $item->load($requestItem, '');
    
            $transaction = RouteReplace::getDb()->beginTransaction();
            try {
                if (!$item->save()) {
                    throw new FormValidationException($item);
                }
        
                $transaction->commit();
                $order++;
                $idsToSave[] = $item->id;
            } finally {
                if ($transaction->getIsActive()) {
                    $transaction->rollBack();
                }
            }
        }
    
        RouteReplace::deleteAll(['AND', 'server_id = :server_id', ['NOT IN', 'id', $idsToSave]], [':server_id' => $serverId]);
    }
}
