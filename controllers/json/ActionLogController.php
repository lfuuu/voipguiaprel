<?php

namespace app\controllers\json;

use app\models\ActionLog;
use app\classes\JsonController;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;

class ActionLogController extends JsonController
{
    protected $doNotLog = true;
    
    public function actionGet()
    {
        if (!\Yii::$app->user->can('action_log_view')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $id   = $this->request['id'];
        $type = $this->request['type'];

        $query = ActionLog::find()
            ->alias('al')
            ->select(['al.*', 'u.name AS user_name'])
            ->innerJoin('auth.user u', 'u.id = al.user_id')
            ->where([
                'object_id'  => $id,
                'controller' => $type,
            ]);

        if (!empty($this->request['action'])) {
            $query->andWhere(['al.action' => $this->request['action']]);
        }

        return $query
            ->orderBy('al.id')
            ->asArray()
            ->all();
    }

    public function actionGetOne()
    {
        if (!\Yii::$app->user->can('action_log_view')) {
            throw new ForbiddenHttpException('Access denied');
        }

        return
            ActionLog::find()
                ->select(['al.*', 'u.name as user_name'])
                ->alias('al')
                ->innerJoin('auth.user u', 'u.id = al.user_id')
                ->where(['al.id' => $this->request['id']])
                ->orderBy('al.id')
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

        $fieldList = [
            'user_id',
            'controller',
            'action'
        ];

        $query = ActionLog::find()
            ->alias('a')
            ->select(['a.*', 'controller' => new Expression('replace(controller, \'json/\', \'\')'), 'u.name as user_name'])
            ->innerJoin('auth.user u', 'u.id = a.user_id')
            ->where('true')
            ->limit($limit)
            ->offset($offset)
            ->asArray();

        $countQuery = ActionLog::find()
            ->select(['id'])
            ->where('true')
            ->distinct();

        foreach ($fieldList as $field) {
            if (isset($searchArray[$field]) && $searchArray[$field] && $searchArray[$field] != 'all') {
                $query->andWhere(['a.'.$field => $searchArray[$field]]);
                $countQuery->andWhere([$field => $searchArray[$field]]);
            }
        }

        if (isset($searchArray['sort_asc'])) {
            if ($searchArray['sort_asc']) {
                $query->orderBy('a.request_date ASC');
            } else {
                $query->orderBy('a.request_date DESC');
            }
        }

        $andWhere = '';
        $params = [];

        if (isset($searchArray['is_time_absolute'])) {
            if ($searchArray['is_time_absolute']) {
                if (empty($searchArray['time_from']) && !empty($searchArray['time_to'])) {
                    $andWhere = 'request_date <= :time_to';
                    $params = [':time_to' => $searchArray['time_to']];
                } elseif (!empty($searchArray['time_from']) && empty($searchArray['time_to'])) {
                    $andWhere = 'request_date >= :time_from';
                    $params = [':time_from' => $searchArray['time_from']];
                } elseif (!empty($searchArray['time_from']) && !empty($searchArray['time_to'])) {
                    $andWhere = 'request_date >= :time_from and request_date <= :time_to';
                    $params = [':time_from' => $searchArray['time_from'], ':time_to' => $searchArray['time_to']];
                }
            } else {
                if (!empty($searchArray['time_relative'])) {
                    $andWhere = 'request_date >= (now() - INTERVAL \'' . (int)$searchArray['time_relative'] . ' seconds\') at time zone \'utc\'';
                }
            }
        }

        if ($andWhere && $params) {
            $query->andWhere($andWhere)->addParams($params);
            $countQuery->andWhere($andWhere)->addParams($params);
        } elseif ($andWhere) {
            $query->andWhere($andWhere);
            $countQuery->andWhere($andWhere);
        }

        $data = $query->all();

        $count = $countQuery->count();

        return [
            'totalCount' => $count,
            'data' => $data
        ];
    }

    public function actionGetControllerList()
    {
        if (!\Yii::$app->user->can('action_log_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        return
            ActionLog::find()
                ->distinct()
                ->select(['id' => 'controller', 'name' => new Expression('replace(controller, \'json/\', \'\')')])
                ->orderBy('controller')
                ->asArray()
                ->all();
    }

    public function actionGetActionList()
    {
        if (!\Yii::$app->user->can('action_log_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        return
            ActionLog::find()
                ->distinct()
                ->select(['id' => 'action', 'name' => 'action'])
                ->orderBy('action')
                ->asArray()
                ->all();
    }
}
