<?php
namespace app\controllers;

use app\classes\BaseController;
use app\classes\ConfigExporter;
use Yii;

class TrunkController extends BaseController
{
    public $layout = false;


    public function actionShow($id, $serverId) {
        $server = $this->getServerOr404($serverId);
        $trunk = $this->getTrunkOr404($id);

        header('Content-Type: text/plain');
        $exp = ConfigExporter::create($trunk, $server);
        $exp->export();
    }

    public function actionDownload($id, $serverId) {
        $server = $this->getServerOr404($serverId);
        $trunk = $this->getTrunkOr404($id);

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=config.txt");
        header("Pragma: no-cache");
        header("Expires: 0");

        $exp = ConfigExporter::create($trunk, $server);
        $exp->export();
    }
}