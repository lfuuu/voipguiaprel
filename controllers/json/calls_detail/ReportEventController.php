<?php
namespace app\controllers\json\calls_detail;

use app\classes\JsonController;
use app\models\calls_detail\ReportEvent;
use yii\web\HttpException;
use yii\web\Response;

class ReportEventController extends JsonController
{
    /**
     * GET /json/calls-detail/report-event?mcn_callid=…
     */
    public function actionIndex()
    {
        $mcn = \Yii::$app->request->get('mcn_callid');
        if (!$mcn) {
            throw new HttpException(400, 'Не указан mcn_callid');
        }

        // Отдаём “сырые” CSV-строки
        \Yii::$app->response->format = Response::FORMAT_RAW;

        $rows = ReportEvent::find()
            ->select(['csv_formated'])
            ->where(['mcn_callid' => $mcn])
            ->orderBy(['dt_create' => SORT_ASC])
            ->asArray()
            ->all();

        $lines = array_map(function($r){
            return rtrim($r['csv_formated'], "\r\n");
        }, $rows);

        return implode("\n", $lines);
    }
}
