<?php

namespace app\controllers\json\a2p_sms;

use app\classes\JsonController;
use app\models\a2p_sms_raw\A2pSmsRaw;
use yii\web\HttpException;

class A2pSmsRawController extends JsonController
{
    public function actionRaw()
    {
        // Получаем cdr_id из GET-параметров (связывается с CDR)
        $cdrId = \Yii::$app->request->get('cdr_id');
        if (!$cdrId) {
            throw new HttpException(400, "Не указан cdr_id");
        }
        $rawData = A2pSmsRaw::find()
            ->where(['cdr_id' => $cdrId])
            ->asArray()
            ->all();
        return $rawData;
    }
}
