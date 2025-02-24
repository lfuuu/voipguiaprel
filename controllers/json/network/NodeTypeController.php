<?php

namespace app\controllers\json\network;

use app\models\calligrapher\NodeType;
use Yii;
use yii\web\HttpException;
use app\classes\BaseController;

class NodeTypeController extends BaseController
{
    public $layout = false;
    public $enableCsrfValidation = false;

    /**
     * Экшен для чтения всех типов узлов.
     * Возвращает JSON-массив всех записей из таблицы calligrapher.node_type.
     *
     * @return array
     */
    public function actionRead()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $types = NodeType::find()->asArray()->all();
        return $types;
    }
}
