<?php

namespace app\controllers\json\calls_detail;

use app\classes\JsonController;
use app\models\calls_detail\ReportCall;
use yii\web\HttpException;
use yii\db\Expression;

class ReportCallController extends JsonController
{
    /**
     * GET /json/calls-detail/report-call?mcn_callid=...
     */
    public function actionIndex()
    {
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
}
