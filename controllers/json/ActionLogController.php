<?php

namespace app\controllers\json;

use app\models\ActionLog;
use app\classes\JsonController;
use yii\web\ForbiddenHttpException;

class ActionLogController extends JsonController
{
    protected $doNotLog = true;
    
    public function actionGet()
    {
        if (!\Yii::$app->user->can('action_log_view')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            ActionLog::find()
                ->select(['al.*', 'u.name as user_name'])
                ->alias('al')
                ->innerJoin('auth.user u', 'u.id = al.user_id')
                ->where(['object_id' => $this->request['id'], 'controller' => $this->request['type']])
                ->orderBy('id')
                ->asArray()
                ->all();
    }
}
