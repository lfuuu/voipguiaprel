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
    $mcn = \Yii::$app->request->get('mcn_callid');
    if (!$mcn) {
        throw new HttpException(400, 'Не указан mcn_callid');
    }

    $isDebug = (bool)\Yii::$app->request->get('is_debug');
    $query = ReportCall::find()->where(['mcn_callid' => $mcn]);

    if ($isDebug) {
        \Yii::$app->response->format = Response::FORMAT_JSON;
        return $query->asArray()->all();
    }

    $results = $query
        ->select(['csv_formated'])
        ->asArray()
        ->all();

    \Yii::$app->response->format = Response::FORMAT_RAW;
    return implode("\n", array_map(function($row) {
        return rtrim($row['csv_formated'], "\r\n");
    }, $results));
}

    

}
