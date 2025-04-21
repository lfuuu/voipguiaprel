<?php

namespace app\controllers\json;

use yii\web\Controller;
use yii\web\HttpException;
use app\models\calls_detail\CallsGlue;
use Yii;

class CallsGlueController extends Controller
{
    public $enableCsrfValidation = false;
    public function actionGetJson()
    {
        $mcnCallid = Yii::$app->request->getBodyParam('mcn_callid');

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
