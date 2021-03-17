<?php
namespace app\controllers;

use app\classes\BaseController;
use app\classes\ConfigExporter;
use Yii;
use yii\web\ForbiddenHttpException;

class RouteCaseController extends BaseController
{
    public $layout = false;

    public function actionExport($serverId)
    {
        if (!\Yii::$app->user->can('route_case_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($serverId);

        header('Content-Type: text/plain');
        $exp = ConfigExporter::create(null, $server);
        $exp->exportRC();

        exit();
    }
}