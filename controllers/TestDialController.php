<?php
namespace app\controllers;

use Yii;
use app\classes\BaseController;

class TestDialController extends BaseController
{
    /**
     * @param int $id
     * @throws \yii\web\HttpException
     */
    public function actionDownload($id) {
        $testDial = $this->getTestDialOr404($id);

        $s3Config = \Yii::$app->params['s3'];
        \S3::setAuth($s3Config["access_key"], $s3Config["secret_key"]);
        \S3::setEndpoint($s3Config["host"]);
        \S3::setSSL($s3Config["use_ssl"]);
        \S3::setExceptions(true);

        $fileName = sprintf('autocall_%s_%s_%s', $id, $testDial->autocall_uuid, date('Y-m-d_H-i'));
        $s3File = \S3::getObject('autocaller', 'autocall/' . $fileName . '.wav');

        header('Content-type: audio/mpeg');
        header('Content-Disposition: attachment; filename=' . $fileName . '.mp3');

        echo $s3File->body;

        exit();
    }
}