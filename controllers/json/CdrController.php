<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\models\billing\DisconnectCause;
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
        $redirectNumber = $this->request['redirect_number'];
        $outRedirectNumber = $this->request['out_redirect_number'];
        $srcRoute = isset($this->request['src_route']) ? $this->request['src_route'] : null;
        $dstRoute = isset($this->request['dst_route']) ? $this->request['dst_route'] : null;
        $isTimeAbsolute = $this->request['is_time_absolute'];
        $timeFrom = $this->request['time_from'];
        $timeTo = $this->request['time_to'];
        $timeRelative = $this->request['time_relative'];
        $limit = $this->request['limit'];
        $hubId = $this->request['hub_id'];
        $mcnCallid = $this->request['mcn_callid'];
        $sortAsc = $this->request['sort_asc'];
        $disconnectCauseId = $this->request['disconnect_cause_id'];
        $showAll = $this->request['show_all'];
        $source = $this->request['source'];
        $sessionTime = $this->request['session_time'];
        $sessionCompare = $this->request['session_compare'];
        
        $where = [];
        
        if ($srcNumber) {
            $where['c.src_number'] = $srcNumber;
        }
        
        if ($dstNumber) {
            $where['c.dst_number'] = $dstNumber;
        }
    
        if ($redirectNumber) {
            $where['c.redirect_number'] = $redirectNumber;
        }

        if ($outRedirectNumber) {
            $where['c.out_redirect_number'] = $outRedirectNumber;
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
    
        if ($disconnectCauseId) {
            $where['c.disconnect_cause'] = $disconnectCauseId;
        }
        
        if (empty($where) && (empty($timeFrom) || empty($timeTo))) {
            return [];
        }
        
        if (empty($limit)) {
            $limit = 100;
        }
        
        $andWhere = '';
        $params = [];
        if ($isTimeAbsolute) {
            if (empty($timeFrom) && !empty($timeTo)) {
                $andWhere = 'c.connect_time <= :time_to';
                $params = [':time_to' => $timeTo];
            } elseif (!empty($timeFrom) && empty($timeTo)) {
                $andWhere = 'c.connect_time >= :time_from';
                $params = [':time_from' => $timeFrom];
            } elseif (!empty($timeFrom) && !empty($timeTo)) {
                $andWhere = 'c.connect_time >= :time_from and c.connect_time <= :time_to';
                $params = [':time_from' => $timeFrom, ':time_to' => $timeTo];
            }
        } else {
            if (!empty($timeRelative)) {
                $andWhere = 'c.connect_time >= (now() - INTERVAL \'' . (int)$timeRelative . ' seconds\') at time zone \'utc\'';
            }
        }
        
        $query = Cdr::find()
            ->alias('c')
            ->select([
                'c.*',
                'server_name' => new Expression("s.id || ': ' || s.name"),
                'disconnect_cause_description' => 'dc.description'
            ])
            ->innerJoin('public.server s', 's.id = c.server_id')
            ->leftJoin('billing.disconnect_cause dc', 'dc.cause_id = c.disconnect_cause')
            ->where($where)
            ->limit($limit)
            ->orderBy('c.connect_time ' . ($sortAsc ? 'ASC' : 'DESC'));
        
        if ($andWhere && $params) {
            $query->andWhere($andWhere)->addParams($params);
        } elseif ($andWhere) {
            $query->andWhere($andWhere);
        }
        
        if (!$showAll) {
            $query->andWhere('c.session_time > 0');
        }
        
        if ($source) {
            switch ($source) {
                case 'xml':
                    $query->andWhere('c.mcn_callid is null');
                    $query->andWhere("c.src_route not like '%Roaming%'");
                    $query->andWhere("c.dst_route not like '%Roaming%'");
                    break;
                case 'accounting':
                    $query->andWhere('c.mcn_callid is not null');
                    break;
                default:
                    break;
            }
        }
        
        if ($sessionCompare && $sessionTime && in_array($sessionCompare, ['>=', '=', '<=']) && is_numeric($sessionTime)) {
            $query->andWhere('c.session_time ' . $sessionCompare . ' ' . $sessionTime);
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
                ->with('callsRaw.currency')
                ->with('callsRaw.legTypeName')
                ->innerJoin('public.server s', 's.id = c.server_id')
                ->leftJoin('billing.disconnect_cause dc', 'dc.cause_id = c.disconnect_cause')
                ->leftJoin('billing.leg_type lt', 'lt.id = c.disconnect_cause')
                ->where(['c.mcn_callid' => $this->request['mcn_callid']])
                ->asArray()
                ->all();
        
        usort($items, function($a, $b) {
            return $a['connect_time'] < $b['connect_time'] ? 1 : -1;
        });
        
        return $items;
    }
    
    public function actionDisconnectCauseList()
    {
        if (!\Yii::$app->user->can('cdr_report_read')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        return
            DisconnectCause::find()
                ->select(['id' => 'cause_id', 'name' => new Expression('cause_id || \': \' || value')])
                ->orderBy('cause_id')
                ->asArray()
                ->all();
    }
}
