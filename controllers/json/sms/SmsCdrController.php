<?php
namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\models\sms_cdr\SmsCdr;
use yii\db\Expression;

class SmsCdrController extends JsonController
{
    public function actionRead()
    {
        $sessionid   = $this->request['sessionid'];
        $msisdn      = $this->request['msisdn'];
        $destination = $this->request['destination'];
        $direction   = $this->request['direction'];
        $serverId    = $this->request['server_id'];
        $limit       = $this->request['limit'] ?: 100;
        $mcc         = $this->request['mcc'];
        $mnc         = $this->request['mnc'];

        $isAbs = $this->request['is_time_absolute'];
        $tFrom = $this->request['time_from'];
        $tTo   = $this->request['time_to'];
        $tRel  = $this->request['time_relative'];

        $where = [];

        if ($sessionid)    $where['c.sessionid']   = $sessionid;
        if ($msisdn)       $where['c.msisdn']    = $msisdn;
        if ($destination)  $where['c.destination'] = $destination;
        if ($serverId)     $where['c.server_id']   = $serverId;
        if (strlen($direction)) $where['c.direction'] = (int)$direction;
        if ($mcc)          $where['c.mcc']         = $mcc;
        if ($mnc)          $where['c.mnc']         = $mnc;

        if (empty($where) && (empty($tFrom) || empty($tTo))) {
            return [];
        }

        $query = SmsCdr::find()->alias('c')
            ->select([
                'c.*',
                'mcc_dict.country AS country',
                'mnc_dict.network AS network',
                new Expression('(SELECT COUNT(*) FROM sms_raw.sms_raw r WHERE r.cdr_id = c.id) AS raw_count')
            ])
            ->leftJoin('nnp.mcc mcc_dict', 'mcc_dict.mcc = c.mcc')
            ->leftJoin('nnp.mnc mnc_dict', 'mnc_dict.mcc = c.mcc AND mnc_dict.mnc = c.mnc')
            ->where($where)
            ->limit($limit)
            ->orderBy('c.dt_create ' . ($isAbs ? 'ASC' : 'DESC'));

        if ($isAbs) {
            if ($tFrom && $tTo) {
                $query->andWhere('c.dt_create BETWEEN :f AND :t', [':f' => $tFrom, ':t' => $tTo]);
            } elseif ($tFrom) {
                $query->andWhere('c.dt_create >= :f', [':f' => $tFrom]);
            } elseif ($tTo) {
                $query->andWhere('c.dt_create <= :t', [':t' => $tTo]);
            }
        } elseif ($tRel) {
            $query->andWhere(new Expression("c.dt_create >= (now() - INTERVAL '{$tRel} seconds')"));
        }

        return $query->asArray()->all();
    }
}
