<?php
namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\models\sms_cdr\SmsCdr;
use yii\web\HttpException;

class SmsCdrController extends JsonController
{
    /**
     * /json/sms/sms-cdr-new/read
     */
    public function actionRead()
    {
        $sessionid  = $this->request['sessionid'];
        $msisdn     = $this->request['msisdn'];
        $destination= $this->request['destination'];
        $direction  = $this->request['direction'];
        $serverId   = $this->request['server_id'];
        $limit      = $this->request['limit'] ?: 100;

        $isAbs      = $this->request['is_time_absolute'];
        $tFrom      = $this->request['time_from'];
        $tTo        = $this->request['time_to'];
        $tRel       = $this->request['time_relative'];

        $where = [];
        if ($sessionid)   $where['c.sessionid']  = $sessionid;
        if ($msisdn)      $where['c.msisdn']     = $msisdn;
        if ($destination) $where['c.destination']= $destination;
        if ($serverId)    $where['c.server_id']  = $serverId;
        if (strlen($direction)) $where['c.direction'] = $direction;

        // если нет временного фильтра — пустой ответ
        if (empty($where) && (empty($tFrom) || empty($tTo))) {
            return [];
        }

        $query = SmsCdr::find()->alias('c')
            ->select([
                'c.*',
                new \yii\db\Expression(
                    '(SELECT COUNT(*) FROM sms_raw.sms_raw r WHERE r.cdr_id = c.id) AS raw_count'
                )
            ])
            ->where($where)
            ->limit($limit)
            ->orderBy('c.dt_create ' . ($isAbs ? 'ASC' : 'DESC'));

        if ($isAbs) {
            if ($tFrom && $tTo) {
                $query->andWhere('c.dt_create BETWEEN :f AND :t', [':f'=>$tFrom, ':t'=>$tTo]);
            } elseif ($tFrom) {
                $query->andWhere('c.dt_create >= :f', [':f'=>$tFrom]);
            } elseif ($tTo) {
                $query->andWhere('c.dt_create <= :t', [':t'=>$tTo]);
            }
        } elseif ($tRel) {
            $query->andWhere("c.dt_create >= (now() - INTERVAL '{$tRel} seconds')");
        }

        return $query->asArray()->all();
    }
}
