<?php
namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\models\a2p_sms_cdr\A2pSmsCdr;
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
        $r = $this->request;

        // Сначала базовый запрос
        $query = A2pSmsCdr::find()->alias('c')->select(['c.*']);

        // Атрибутные фильтры (если поле не задано — пропустит)
        $query->andFilterWhere([
            'c.server_id'  => $r['server_id'],
            'c.sms_id'     => $r['sms_id'],
            'c.src_number' => $r['src_number'],
            'c.dst_number' => $r['dst_number'],
            'c.src_route'  => $r['src_route'],
            'c.dst_route'  => $r['dst_route'],
            'c.status'     => $r['status'],
        ]);

        // Время
        $isAbs = $r['is_time_absolute'];
        $f     = $r['time_from'];
        $t     = $r['time_to'];
        $rel   = $r['time_relative'];

        if ($isAbs) {
            if ($f && $t) {
                $query->andWhere('c.dt_create BETWEEN :f AND :t', [':f'=>$f, ':t'=>$t]);
            } elseif ($f) {
                $query->andWhere('c.dt_create >= :f', [':f'=>$f]);
            } elseif ($t) {
                $query->andWhere('c.dt_create <= :t', [':t'=>$t]);
            }
        } elseif ($rel) {
            $query->andWhere(new Expression("c.dt_create >= (now() - INTERVAL '{$rel} seconds')"));
        }

        // Лимит и сортировка
        $limit = $r['limit'] ?: 100;
        $query->limit($limit)
              ->orderBy('c.dt_create ' . ($isAbs ? 'ASC' : 'DESC'));

        return $query->asArray()->all();
    }
}
