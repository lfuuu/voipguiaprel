<?php

namespace app\controllers\json\calls_detail;

use app\classes\JsonController;
use app\models\calls_detail\ReportCall;
use yii\web\HttpException;
use yii\web\Response;
use yii\db\Expression;
use yii\db\Query;

class ReportCallController extends JsonController
{
    /**
     * GET /json/calls-detail/report-call?mcn_callid=...
     */
    public function actionIndex()
{
    \Yii::$app->response->format = Response::FORMAT_JSON;
    $mcn = \Yii::$app->request->get('mcn_callid');
    if (!$mcn) {
        throw new HttpException(400, 'Не указан mcn_callid');
    }
    $isDebug = (bool)\Yii::$app->request->get('is_debug');

    $query = ReportCall::find()->where(['mcn_callid' => $mcn]);

    if ($isDebug) {
        // вернёт всё
        return $query->asArray()->all();
    }

    // иначе штатный отбор
    return $query
        ->select([
            'call_type_id',
            'csv_formated',
            new Expression("
                concat(
                  'Оригинационный Транк: ', incoming_trunk_debug,
                  '; Терминационный Транк: ', outgoing_trunk_debug,
                  '; Нода: ', switch_name_id_debug
                ) AS trunks_info
            ")
        ])
        ->asArray()
        ->all();
}

}
