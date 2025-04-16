<?php

namespace app\controllers\json;

use yii\web\Controller;
use yii\web\HttpException;
use app\models\calls_detail\CallsGlue;
use Yii;

class CallsGlueController extends Controller
{
    public function actionGetJson()
    {
        // Извлекаем параметр из тела POST-запроса, где данные передаются в формате JSON
        $mcnCallid = Yii::$app->request->getBodyParam('mcn_callid');

        Yii::info('Received mcn_callid: ' . var_export($mcnCallid, true), __METHOD__);

        if (!$mcnCallid) {
            throw new HttpException(400, "Не указан mcn_callid");
        }

        $data = CallsGlue::find()
            ->where(['mcn_callid' => $mcnCallid])
            ->asArray()
            ->one();

        if (!$data) {
            throw new HttpException(404, "Данные не найдены");
        }

        return $data['val'];
    }
}
