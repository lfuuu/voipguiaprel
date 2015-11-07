<?php
namespace app\controllers;

use app\classes\BaseController;
use app\classes\ConfigExporter;
use Yii;

class RouteCaseController extends BaseController
{
    public $layout = false;

    public function actionExport($serverId) {
        $server = $this->getServerOr404($serverId);

        header('Content-Type: text/plain');
        $exp = ConfigExporter::create(null, $server);
        $exp->exportRC();
    }
}