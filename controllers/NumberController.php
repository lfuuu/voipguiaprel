<?php
namespace app\controllers;

use app\classes\BaseController;
use app\classes\ConfigExporter;
use Yii;

class NumberController extends BaseController
{
    public $layout = false;

    public function actionExport($serverId, $numberId, $airp, $outcome, $outcomeNext) {
        $server = $this->getServerOr404($serverId);
        $number = $this->getNumberOr404($numberId);


        header('Content-Type: text/plain');
        $exp = ConfigExporter::create(null, $server);
        $exp->exportNumber($number->id, $airp, $outcome, $outcomeNext);
    }
}