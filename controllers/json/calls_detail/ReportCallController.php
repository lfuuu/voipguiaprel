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
            return $query->asArray()->all();
        }
    
        $results = $query
            ->select(['csv_formated'])
            ->asArray()
            ->all();
    
        return array_map(function($row) {
            return rtrim($row['csv_formated'], "\r\n");
        }, $results);
    }
    

}
