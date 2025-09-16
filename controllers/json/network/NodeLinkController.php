<?php

namespace app\controllers\json\network;

use app\models\calligrapher\NodeLink;
use Yii;
use yii\web\HttpException;
use app\classes\BaseController;

class NodeLinkController extends BaseController
{
    public $layout = false;
    public $enableCsrfValidation = false;

    /**
     * Список всех ссылок (транков) в calligrapher.node_link
     * Возвращает JSON‑массив
     */
    public function actionRead()
{
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $links = NodeLink::find()
        ->alias('l')
        ->select([
            'l.node_link_id',
            'l.src_node_id',
            'l.dst_node_id',
            'l.sorm_id',
            'l.weight',
            'l.comment',
            'l.sorm_name AS sorm_name',
            "CONCAT(src.node_name_id, ' - ', src.node_id) AS src_node_display",
            "CONCAT(dst.node_name_id, ' - ', dst.node_id) AS dst_node_display",
        ])
        ->leftJoin('calligrapher.node src', 'src.node_id = l.src_node_id')
        ->leftJoin('calligrapher.node dst', 'dst.node_id = l.dst_node_id')
        ->asArray()
        ->all();

    return $links;
}



    /**
     * Получение одной записи node_link по ID
     */
    public function actionGet()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $postData = Yii::$app->request->post();
        if (empty($postData['id'])) {
            throw new HttpException(400, 'Не передан ID');
        }
        $link = $this->getLinkOr404($postData['id']);
        return $link->toArray();
    }

    /**
     * Создание/обновление
     * Если не передан node_link_id, создаём новую запись
     */
    public function actionSave()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $postData = Yii::$app->request->post();

        // Допустим, у вас есть права trunk_edit или node_link_edit
        // if (!Yii::$app->user->can('node_link_edit')) {
        //     throw new \yii\web\ForbiddenHttpException('Access denied.');
        // }

        if (!empty($postData['node_link_id'])) {
            $link = $this->getLinkOr404($postData['node_link_id']);
        } else {
            $link = new NodeLink();
        }

        $link->load($postData, '');
        if ($link->save()) {
            return [
                'success' => true,
                'node_link_id' => $link->node_link_id,
                'message' => 'Транк успешно сохранён',
            ];
        }
        return [
            'success' => false,
            'errors' => $link->errors,
        ];
    }

    /**
     * Удаление по ID
     */
    public function actionDelete()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $postData = Yii::$app->request->post('id');
        if (empty($postData)) {
            return ['success' => false, 'message' => 'Не передан ID'];
        }
        $link = $this->getLinkOr404($postData);
        if ($link->deleteRecord() !== false) {
            return [
                'success' => true,
                'message' => 'Транк удалён.',
            ];
        }
        return [
            'success' => false,
            'message' => 'Не удалось удалить транк.',
        ];
    }

    protected function getLinkOr404($id)
    {
        $link = NodeLink::findOne($id);
        if ($link === null) {
            throw new HttpException(404, 'NodeLink не найден');
        }
        return $link;
    }
}
