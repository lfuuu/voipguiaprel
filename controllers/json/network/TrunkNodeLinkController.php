<?php

namespace app\controllers\json\network;

use app\models\calligrapher\TrunkNodeLink;
use Yii;
use app\classes\BaseController;

class TrunkNodeLinkController extends BaseController
{
    public $layout = false;
    public $enableCsrfValidation = false;

    /**
     * Экшен для получения списка связей транков и узлов.
     * Возвращает JSON-массив всех записей из таблицы calligrapher.trunk_node_link.
     *
     * @return array
     */
    public function actionRead()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $links = \app\models\calligrapher\TrunkNodeLink::find()
        ->alias('t')
        ->select([
            't.trunk_node_link_id',
            't.service_trunk_id',
            't.node_id',
            't.orig',
            't.contract_type_id',
            't.comment',
            "COALESCE(cct.name, 'Не задан') AS contract_type_text"
        ])
        ->leftJoin('stat.client_contract_type cct', 't.contract_type_id = cct.id')
        ->asArray()
        ->all();

    return $links;
}


}
