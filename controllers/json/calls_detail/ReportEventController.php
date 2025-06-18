<?php
namespace app\controllers\json\calls_detail;

use app\classes\JsonController;
use app\models\calls_detail\ReportEvent;
use yii\web\HttpException;
use yii\web\Response;

class ReportEventController extends JsonController
{
    /**
     * GET /json/calls_detail/report-event?mcn_callid=…[&is_debug=1]
     */
    public function actionIndex()
    {
        $mcn = \Yii::$app->request->get('mcn_callid');
        if (!$mcn) {
            throw new HttpException(400, 'Не указан mcn_callid');
        }

        $query = ReportEvent::find()->where(['mcn_callid' => $mcn]);

        // debug‐режим: отдать всю строку(и) как JSON
        if ((bool)\Yii::$app->request->get('is_debug')) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            return $query->asArray()->all();
        }

        // обычный режим: возвращаем raw CSV
        \Yii::$app->response->format = Response::FORMAT_RAW;
        $rows = $query->select(['csv_formated'])->asArray()->all();
        $lines = array_map(function($r){
            return rtrim($r['csv_formated'], "\r\n");
        }, $rows);
        return implode("\n", $lines);
    }
}
