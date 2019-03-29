<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\models\calls_cdr\Cdr;
use yii\base\Request;
use yii\db\Expression;
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
        $hubId = $this->request['hub_id'];
        $mcnCallid = $this->request['mcn_callid'];
        
        $where = [];
        
        if ($srcNumber) {
            $where['c.src_number'] = $srcNumber;
        }
        
        if ($dstNumber) {
            $where['c.dst_number'] = $dstNumber;
        }
    
        if ($hubId) {
            $where['s.hub_id'] = $hubId;
        }
        
        if ($mcnCallid) {
            $where['c.mcn_callid'] = $mcnCallid;
        }
    
        if ($srcRoute) {
            $where['c.src_route'] = $srcRoute;
        }
    
        if ($dstRoute) {
            $where['c.dst_route'] = $dstRoute;
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
            $andWhere = 'c.setup_time <= :time_to';
            $params = [':time_to' => $timeTo];
        } elseif (!empty($timeFrom) && empty($timeTo)) {
            $andWhere = 'c.setup_time >= :time_from';
            $params = [':time_from' => $timeFrom];
        } elseif (!empty($timeFrom) && !empty($timeTo)) {
            $andWhere = 'c.setup_time >= :time_from and c.setup_time <= :time_to';
            $params = [':time_from' => $timeFrom, ':time_to' => $timeTo];
        }
        
        $query = Cdr::find()
            ->alias('c')
            ->select([
                'c.*',
                'server_name' => new Expression("s.id || ': ' || s.name"),
                'disconnect_cause_description' => 'dc.description'
            ])
            ->innerJoin('public.server s', 's.id = c.server_id')
            ->innerJoin('billing.disconnect_cause dc', 'dc.cause_id = c.disconnect_cause')
            ->where($where)
            ->limit($limit)
            ->orderBy('c.connect_time');
        
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
        
        $items = Cdr::find()
                ->alias('c')
                ->select([
                    'c.*',
                    'server_name' => new Expression("s.id || ': ' || s.name"),
                    'disconnect_cause_description' => 'dc.description',
                ])
                ->with('callsRaw')
                ->innerJoin('public.server s', 's.id = c.server_id')
                ->innerJoin('billing.disconnect_cause dc', 'dc.cause_id = c.disconnect_cause')
                ->where(['c.mcn_callid' => $this->request['mcn_callid']])
                ->orderBy('c.connect_time')
                ->asArray()
                ->all();
        
        return $items;
    }
}
