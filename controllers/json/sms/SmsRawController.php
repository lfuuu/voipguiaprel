<?php
namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\models\sms_raw\SmsRaw;
use yii\web\HttpException;

class SmsRawController extends JsonController
{
    public function actionRaw()
    {
        $cdrId = \Yii::$app->request->get('cdr_id');
        if (!$cdrId) {
            throw new HttpException(400, 'Не указан cdr_id');
        }

        return SmsRaw::find()
            ->where(['cdr_id' => $cdrId])
            ->asArray()
            ->all();
    }
}
