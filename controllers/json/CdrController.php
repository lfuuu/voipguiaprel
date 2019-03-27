<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\models\calls_cdr\Cdr;
use yii\base\Request;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class CdrController extends JsonController
{
    public function actionRead()
    {
        if (!\Yii::$app->user->can('cdr_report_read')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $srcNumber = $this->request['src_number'];
        $dstNumber = $this->request['dst_number'];
        $srcRoute = isset($this->request['src_route']) ? $this->request['src_route'] : null;
        $dstRoute = isset($this->request['dst_route']) ? $this->request['dst_route'] : null;
        $timeFrom = $this->request['time_from'];
        $timeTo = $this->request['time_to'];
        $limit = $this->request['limit'];
        $serverId = $this->request['server_id'];
        $mcnCallid = $this->request['mcn_callid'];
        
        $where = [];
        
        if ($srcNumber) {
            $where['src_number'] = $srcNumber;
        }
        
        if ($dstNumber) {
            $where['dst_number'] = $dstNumber;
        }
    
        if ($serverId) {
            $where['server_id'] = $serverId;
        }
        
        if ($mcnCallid) {
            $where['mcn_callid'] = $mcnCallid;
        }
    
        if ($srcRoute) {
            $where['src_route'] = $srcRoute;
        }
    
        if ($dstRoute) {
            $where['dst_route'] = $dstRoute;
        }
        
        if (empty($where) && (empty($timeFrom) || empty($timeTo))) {
            return [];
        }
        
        if (empty($limit)) {
            $limit = 100;
        }
        
        $andWhere = '';
        $params = [];
        
        if (empty($timeFrom) && !empty($timeTo)) {
            $andWhere = 'setup_time <= :time_to';
            $params = [':time_to' => $timeTo];
        } elseif (!empty($timeFrom) && empty($timeTo)) {
            $andWhere = 'setup_time >= :time_from';
            $params = [':time_from' => $timeFrom];
        } elseif (!empty($timeFrom) && !empty($timeTo)) {
            $andWhere = 'setup_time >= :time_from and setup_time <= :time_to';
            $params = [':time_from' => $timeFrom, ':time_to' => $timeTo];
        }
        
        $query = Cdr::find()
                    ->select(['*'])
                    ->where($where)
                    ->limit($limit)
                    ->orderBy('id');
        
        if ($andWhere && $params) {
            $query->andWhere($andWhere)->addParams($params);
        }
        
        return $query->asArray()->all();
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('cdr_report_read')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            Cdr::find()
                ->select(['*'])
                ->where(['mcn_callid' => $this->request['mcn_callid']])
                ->orderBy('id')
                ->asArray()
                ->all();
    }
}
