<?php
namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\models\smsc_raw\SmscRaw;
use yii\web\HttpException;

class SmscRawController extends JsonController
{
    public function actionRaw()
    {
        $smppCdrId = \Yii::$app->request->get('smpp_cdr_id');
        if (!$smppCdrId) {
            throw new HttpException(400, 'Не указан smpp_cdr_id');
        }
        return SmscRaw::find()
            ->where(['or',
                ['smpp_cdr_id' => $smppCdrId],
                ['orig_cdr_id' => $smppCdrId],
                ['term_cdr_id' => $smppCdrId],
            ])
            ->asArray()
            ->all();
    }
}
