<?php
namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\models\a2p_sms_cdr\A2pSmsCdr;
use yii\web\HttpException;
use yii\web\Response;
use yii\db\Expression;

class A2pSmsCdrController extends JsonController
{
    /**
     * POST /json/sms/a2p-sms-cdr/read
     */
    public function actionRead()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $r      = $this->request;
        $where  = [];

        if ($r['server_id'])   $where['c.server_id']   = $r['server_id'];
        if ($r['sms_id'])      $where['c.sms_id']      = $r['sms_id'];
        if ($r['src_number'])  $where['c.src_number']  = $r['src_number'];
        if ($r['dst_number'])  $where['c.dst_number']  = $r['dst_number'];
        if ($r['src_route'])   $where['c.src_route']   = $r['src_route'];
        if ($r['dst_route'])   $where['c.dst_route']   = $r['dst_route'];
        if ($r['status'])      $where['c.status']      = $r['status'];

        $limit  = $r['limit'] ?: 100;
        $isAbs  = $r['is_time_absolute'];
        $f      = $r['time_from'];
        $t      = $r['time_to'];
        $rel    = $r['time_relative'];

        if (empty($where) && (!$f || !$t)) {
            return [];
        }

        $q = A2pSmsCdr::find()->alias('c')
            ->select(['c.*'])
            ->where($where)
            ->limit($limit)
            ->orderBy('c.dt_create ' . ($isAbs ? 'ASC' : 'DESC'));

        if ($isAbs) {
            if ($f && $t)      $q->andWhere('c.dt_create BETWEEN :f AND :t', [':f'=>$f, ':t'=>$t]);
            elseif ($f)       $q->andWhere('c.dt_create >= :f', [':f'=>$f]);
            elseif ($t)       $q->andWhere('c.dt_create <= :t', [':t'=>$t]);
        } elseif ($rel) {
            $q->andWhere(new Expression("c.dt_create >= (now() - INTERVAL '{$rel} seconds')"));
        }

        return $q->asArray()->all();
    }
}
