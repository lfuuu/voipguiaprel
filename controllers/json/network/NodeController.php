<?php

namespace app\controllers\json\network;

use app\models\calligrapher\Node;
use app\models\calligrapher\NodeLink;
use app\models\calligrapher\NodeStatus;
use app\models\calligrapher\NodeType;
use app\models\calligrapher\RussianCity;
use app\models\calligrapher\RussianDistrict;
use app\models\calligrapher\RussianSubject;
use app\models\calligrapher\TrunkNodeLink;
use Yii;
use yii\web\HttpException;
use app\classes\BaseController;
use app\classes\ConfigExporter;

class NodeController extends BaseController
{
    public $layout = false;
    public $enableCsrfValidation = false;

    /**
     * Вывод конфигурации узла в виде plain text.
     */
    public function actionShow($id, $serverId)
    {
        $server = $this->getServerOr404($serverId);
        $node = $this->getNodeOr404($id);

        header('Content-Type: text/plain');
        $exp = ConfigExporter::create($node, $server);
        $exp->export();
        exit();
    }

    /**
     * Скачать конфигурацию узла в виде файла.
     */
    public function actionDownload($id, $serverId)
    {
        $server = $this->getServerOr404($serverId);
        $node = $this->getNodeOr404($id);

        $fileName = sprintf('config_%s_%s_%s.txt', $id, $serverId, date('Y-m-d_H-i'));
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename=' . $fileName);
        header('Pragma: no-cache');
        header('Expires: 0');

        $exp = ConfigExporter::create($node, $server);
        $exp->export();
        exit();
    }

    /**
     * Отобразить полную информацию об узле.
     */
    public function actionFullInfo($nodeId)
    {
        $node = $this->getNodeOr404($nodeId);
        $this->layout = 'minimal';
        return $this->render('full-info', [
            'node' => $node,
        ]);
    }

    public function actionGet()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $postData = Yii::$app->request->post();
        if (empty($postData['id'])) {
            throw new HttpException(400, 'ID не передан');
        }
        $node = $this->getNodeOr404($postData['id']);
        return $node->toArray();
    }

    /**
     * Новый экшен для чтения всех узлов с текстовыми описаниями.
     */
    public function actionRead()
    {
        if (!\Yii::$app->user->can('network_list')) {
            throw new \yii\web\ForbiddenHttpException('Access denied.');
        }
        
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $nodes = Node::find()
            ->alias('n')
            ->select([
                'n.*',
                'nt.node_type AS node_type_text',
                'ns.node_status AS node_status_text',
                'rd.russian_district AS district_text',
                'rs.russian_subject AS subject_text',
                'rc.russian_city AS city_text',
                'n.net_type',
                'n.ss7_spc',
            ])
            ->leftJoin('calligrapher.node_type nt', 'n.node_type_id = nt.node_type_id')
            ->leftJoin('calligrapher.node_status ns', 'n.node_status_id = ns.node_status_id')
            ->leftJoin('calligrapher.russian_district rd', 'n.russian_district_id = rd.russian_district_id')
            ->leftJoin('calligrapher.russian_subject rs', 'n.russian_subject_id = rs.russian_subject_id')
            ->leftJoin('calligrapher.russian_city rc', 'n.russian_city_id = rc.russian_city_id')
            ->asArray()
            ->all();
        
        return $nodes;
    }
    

    /**
     * Создание или обновление узла.
     */
    public function actionSave()
    {
        if (!\Yii::$app->user->can('network_edit')) {
            throw new \yii\web\ForbiddenHttpException('Access denied.');
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $postData = Yii::$app->request->post();

        if (!empty($postData['node_id'])) {
            $node = $this->getNodeOr404($postData['node_id']);
        } else {
            $node = new Node();
        }

        $node->load($postData, '');

        if ($node->save()) {
            return [
                'success' => true,
                'node_id' => $node->node_id,
                'message' => 'Узел успешно сохранён'
            ];
        }

        return [
            'success' => false,
            'errors' => $node->errors,
        ];
    }

    /**
     * Удаление узла по ID.
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('network_edit')) {
            throw new \yii\web\ForbiddenHttpException('Access denied.');
        }
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $postData = Yii::$app->request->post('id');

        if (empty($postData)) {
            return [
                'success' => false,
                'message' => 'Не передан ID узла.'
            ];
        }

        $node = $this->getNodeOr404($postData);
        if ($node->deleteRecord() !== false) {
            return [
                'success' => true,
                'message' => 'Узел удалён.'
            ];
        }

        return [
            'success' => false,
            'message' => 'Не удалось удалить узел.'
        ];
    }

    /**
     * Экшен для получения списка типов узлов.
     */
    public function actionListNodeTypes()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return NodeType::find()->asArray()->all();
    }

    /**
     * Экшен для получения списка статусов узлов.
     */
    public function actionListNodeStatuses()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return NodeStatus::find()->asArray()->all();
    }

    public function actionListServers()
{
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $servers = (new \yii\db\Query())
        ->select([
            'id',
            'name',
            "CONCAT(name, ' - ', id) AS name_display"
        ])
        ->from('public.server')
        ->where(['is_visible' => true])
        ->all();
        
    return $servers;
}

    /**
     * Экшен для получения списка городов РФ.
     */
    public function actionListRussianCities()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return RussianCity::find()->asArray()->all();
    }

    /**
     * Экшен для получения списка федеральных округов (районов) РФ.
     */
    public function actionListRussianDistricts()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return RussianDistrict::find()->asArray()->all();
    }

    /**
     * Экшен для получения списка субъектов РФ.
     */
    public function actionListRussianSubjects()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return RussianSubject::find()->asArray()->all();
    }

    public function actionListNodesForLink()
{
    \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $nodes = (new \yii\db\Query())
        ->select([
            'node_id as id',
            "CONCAT(node_name_id, ' - ', node_id) as name_display"
        ])
        ->from('calligrapher.node')
        ->orderBy('node_name_id')
        ->all();

    return $nodes;
}



    /**
     * Экшен для получения списка связей узлов (node_link).
     */
    public function actionListNodeLinks()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return NodeLink::find()->asArray()->all();
    }

    /**
     * Экшен для получения списка связей транков и узлов.
     */
    public function actionListTrunkNodeLinks()
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return TrunkNodeLink::find()->asArray()->all();
    }

    /**
     * Получить объект Node по идентификатору или выбросить исключение 404.
     */
    protected function getNodeOr404($id)
    {
        $node = Node::findOne($id);
        if ($node === null) {
            throw new HttpException(404, 'Узел не найден');
        }
        return $node;
    }
}
