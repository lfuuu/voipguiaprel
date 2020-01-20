<?php
namespace app\controllers;

use Yii;
use app\classes\BaseController;

class TestDialController extends BaseController
{
    /**
     * @param int $id
     * @param $serverId
     * @throws \yii\web\HttpException
     */
    public function actionDownload($id) {
        $testDial = $this->getTestDialOr404($id);

        $s3Config = \Yii::$app->params['s3'];
        \S3::setAuth($s3Config["access_key"], $s3Config["secret_key"]);
        \S3::setEndpoint($s3Config["host"]);
        \S3::setSSL($s3Config["use_ssl"]);
        \S3::setExceptions(true);

        $s3File = \S3::getObject('autocaller', 'autocall/' . $testDial->autocall_uuid . '.wav');

        header('Content-type: audio/mpeg');
        header('Content-Disposition: attachment; filename=' . $testDial->autocall_uuid . '.mp3');

        echo $s3File->body;

        die;
    }
}