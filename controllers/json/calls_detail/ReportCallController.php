<?php

namespace app\controllers\json\calls_detail;

use app\classes\JsonController;
use app\models\calls_detail\ReportCall;
use yii\web\HttpException;

class ReportCallController extends JsonController
{
    /**
     * GET /json/calls-detail/report-call?mcn_callid=...
     * Возвращает все записи call_type_id и csv_formated для заданного mcn_callid
     */
    public function actionIndex()
    {
        $mcn = \Yii::$app->request->get('mcn_callid');
        if (!$mcn) {
            throw new HttpException(400, 'Не указан mcn_callid');
        }

        return ReportCall::find()
            ->select(['call_type_id', 'csv_formated'])
            ->where(['mcn_callid' => $mcn])
            ->asArray()
            ->all();
    }
}
