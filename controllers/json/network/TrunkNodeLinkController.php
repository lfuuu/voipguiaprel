<?php

namespace app\controllers\json\network;

use app\models\calligrapher\TrunkNodeLink;
use Yii;
use app\classes\BaseController;
use yii\web\HttpException;

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
                't.id_phys_trunk',
                't.node_id',
                't.comment',
                "CONCAT(n.node_name_id, ' - ', n.node_id) AS node_display",
                "COALESCE(cct.name, 'Не задан') AS contract_type_text",
                't.contract_type_id',
                'st.trunk_id',
                'st.client_account_id',
                'st.description AS description',
                'c.contragent_name AS contragent_name'
            ])
            ->leftJoin('calligrapher.node n', 'n.node_id = t.node_id')
            ->leftJoin('billing.service_trunk st', 't.service_trunk_id = st.id')
            ->leftJoin('stat.client_contract_type cct', 'st.contract_type_id = cct.id')
            ->leftJoin('billing.clients c', 'st.client_account_id = c.id')
            ->asArray()
            ->all();
    
        return $links;
    }
    

    /**
     * Экшен для получения одной записи связи транка по ID.
     *
     * @return array
     * @throws HttpException
     */
    public function actionGet()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $id = Yii::$app->request->post('id');
        if (empty($id)) {
            throw new HttpException(400, 'Не передан ID связи транка');
        }
        $link = TrunkNodeLink::findOne($id);
        if ($link === null) {
            throw new HttpException(404, 'Связь транка не найдена');
        }
        return $link->toArray();
    }

    /**
     * Экшен для создания или обновления связи транка.
     * Если передан trunk_node_link_id, обновляем существующую запись, иначе создаём новую.
     *
     * @return array
     * @throws HttpException
     */
    public function actionSave()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $postData = Yii::$app->request->post();

        if (!empty($postData['trunk_node_link_id'])) {
            $link = TrunkNodeLink::findOne($postData['trunk_node_link_id']);
            if ($link === null) {
                throw new HttpException(404, 'Связь транка не найдена');
            }
        } else {
            $link = new TrunkNodeLink();
        }

        $link->load($postData, '');
        if ($link->save()) {
            return [
                'success' => true,
                'trunk_node_link_id' => $link->trunk_node_link_id,
                'message' => 'Связь транка успешно сохранена'
            ];
        }

        return [
            'success' => false,
            'errors' => $link->errors,
        ];
    }
}
