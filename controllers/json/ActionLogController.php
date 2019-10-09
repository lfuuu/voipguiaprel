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

    public function actionRead()
    {
        if (!\Yii::$app->user->can('action_log_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $searchArray = $this->request['search_array'];
        $limit = $this->request['limit'];
        $offset = $this->request['offset'];

        $query = ActionLog::find()
            ->alias('a')
            ->limit($limit)
            ->offset($offset)
            ->asArray();

        $countQuery = ActionLog::find()
            ->select(['id'])
            ->distinct();

        if (isset($searchArray['user_id']) && $searchArray['user_id'] && $searchArray['user_id'] != 'all') {
            $query->where(['a.user_id' => $searchArray['user_id']]);
            $countQuery->where(['user_id' => $searchArray['user_id']]);
        }

        $data = $query->all();

        $count = $countQuery->count();

        return [
            'totalCount' => $count,
            'data' => $data
        ];
    }
}
