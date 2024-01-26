<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\ReleaseReason;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class ReleaseReasonController extends JsonController
{
    const DEFAULT_OCS_SERVER_ID = 9;

    public function actionList()
    {
        if (!\Yii::$app->user->can('release_reason_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $isOcs = $this->request['server_id'] == self::DEFAULT_OCS_SERVER_ID;
        $server = $isOcs ? $this->getServerOcsOr404($this->request['server_id']) : $this->getServerOr404($this->request['server_id']);

        $where = "server_id = ".$server->id;
        if (!$isOcs) {
            $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;
            $where = "( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id;
        }
 
        return
            ReleaseReason::find()
                ->select(['id', 'name'])
                ->where($where)
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead()
    {
        if (!\Yii::$app->user->can('release_reason_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;


        return
            ReleaseReason::find()
                ->select(['id', 'name', 'server_id', 'sw_shared', 'object_comment'])
                ->where("( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id)
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('release_reason_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = ReleaseReason::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'Release reason не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('release_reason_edit') && !\Yii::$app->user->can('release_reason_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $result = [];
        
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('release_reason_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $releaseReason = $this->getReleaseReasonOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($releaseReason)];
        } else {
            if (!\Yii::$app->user->can('release_reason_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $releaseReason = ReleaseReason::create($server);
            $result['log'] = ['data_before' => []];
        }

        $releaseReason->load($this->request, '');

        $transaction = ReleaseReason::getDb()->beginTransaction();
        try {
            if (!$releaseReason->save()) {
                throw new FormValidationException($releaseReason);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    
        $result['log']['data_after'] = $this->getDataForLog($releaseReason);
    
        return $result;
    }

    public function actionDelete()
    {
        if (!\Yii::$app->user->can('release_reason_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = ReleaseReason::findOne($this->request['id']);
        $item->delete();
    }
}
