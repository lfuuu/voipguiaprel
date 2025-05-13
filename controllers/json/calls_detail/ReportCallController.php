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

        return ReportCall::find()
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
            ->where(['mcn_callid' => $mcn])
            ->asArray()
            ->all();
    }

    /**
     * GET /json/calls-detail/report-call/debug?mcn_callid=...
     * Debug-режим: возвращает ВСЕ поля из таблицы report_call
     */
    public function actionDebug()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        $mcn = \Yii::$app->request->get('mcn_callid');
        if (!$mcn) {
            throw new HttpException(400, 'Не указан mcn_callid');
        }

        return ReportCall::find()
            ->where(['mcn_callid' => $mcn])
            ->asArray()
            ->all();
    }
}
