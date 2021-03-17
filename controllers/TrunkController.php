<?php
namespace app\controllers;

use app\models\Trunk;
use Yii;
use yii\web\HttpException;
use app\classes\BaseController;
use app\classes\ConfigExporter;

class TrunkController extends BaseController
{
    public $layout = false;

    /**
     * @param int $id
     * @param int $serverId
     * @throws \yii\web\HttpException
     */
    public function actionShow($id, $serverId) {
        $server = $this->getServerOr404($serverId);
        $trunk = $this->getTrunkOr404($id);

        header('Content-Type: text/plain');
        $exp = ConfigExporter::create($trunk, $server);
        $exp->export();

        exit();
    }

    /**
     * @param int $id
     * @param $serverId
     * @throws \yii\web\HttpException
     */
    public function actionDownload($id, $serverId) {
        $server = $this->getServerOr404($serverId);
        $trunk = $this->getTrunkOr404($id);

        $fileName = sprintf('config_%s_%s_%s.txt', $id, $serverId, date('Y-m-d_H-i'));
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Pragma: no-cache');
        header('Expires: 0');

        $exp = ConfigExporter::create($trunk, $server);
        $exp->export();

        exit();
    }

    /**
     * @param int $trunkId
     * @return string
     * @throws \yii\web\HttpException
     */
    public function actionFullInfo($trunkId)
    {
        $trunk = $this->getTrunkOr404($trunkId);

        $this->layout = 'minimal';
        return $this->render('full-info', [
            'trunk' => $trunk,
        ]);
    }

}