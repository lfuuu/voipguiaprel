<?php
namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\models\a2p_sms_raw\A2pSmsRaw;
use yii\web\HttpException;
use yii\web\Response;

class A2pSmsRawController extends JsonController
{
    /**
     * GET /json/sms/a2p-sms-raw/raw?cdr_id=...
     */
    public function actionRaw()
    {
        \Yii::$app->response->format = Response::FORMAT_JSON;

        $cdrId = \Yii::$app->request->get('cdr_id');
        if (!$cdrId) {
            throw new HttpException(400, 'Не указан cdr_id');
        }

        return A2pSmsRaw::find()
            ->where(['cdr_id' => $cdrId])
            ->asArray()
            ->all();
    }
}
