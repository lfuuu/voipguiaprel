<?php
namespace app\controllers;

use app\classes\BaseController;
use app\classes\ConfigExporter;
use app\classes\ConfigVersionCloner;
use app\dao\ConfigVersionDao;
use app\models\ConfigVersion;
use Yii;
use yii\web\HttpException;

class TrunkController extends BaseController
{
    public $layout = false;


    public function actionShow($id, $versionId) {
        $version = $this->getVersionOr404($versionId);
        $trunk = $this->getTrunkOr404($id);

        header('Content-Type: text/plain');
        $exp = ConfigExporter::create($trunk, $version);
        $exp->export();
    }

    public function actionDownload($id, $versionId) {
        $version = $this->getVersionOr404($versionId);
        $trunk = $this->getTrunkOr404($id);

        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=config.txt");
        header("Pragma: no-cache");
        header("Expires: 0");

        $exp = ConfigExporter::create($trunk, $version);
        $exp->export();
    }
}