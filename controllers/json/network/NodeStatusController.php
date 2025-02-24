<?php

namespace app\controllers\json\network;

use app\models\calligrapher\NodeStatus;
use Yii;
use yii\web\HttpException;
use app\classes\BaseController;

class NodeStatusController extends BaseController
{
    public $layout = false;
    public $enableCsrfValidation = false;

    /**
     * Экшен для получения списка статусов узлов.
     * Возвращает JSON-массив всех записей из таблицы calligrapher.node_status.
     *
     * @return array
     */
    public function actionRead()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $statuses = NodeStatus::find()->asArray()->all();
        return $statuses;
    }
}
