<?php

namespace app\controllers\json\network;

use Yii;
use yii\web\HttpException;
use app\classes\BaseController;

class TrunkNodeLinkController extends BaseController
{
    public $layout = false;
    public $enableCsrfValidation = false;

    /**
     * Возвращает список связей транков и узлов вместе с ip_address из copm.trunk.
     *
     * @return array
     */
    public function actionRead()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $sql = <<<SQL
WITH trunk_ip AS (
    SELECT DISTINCT ON (code_trunk) 
        code_trunk, 
        ip_addr
    FROM copm.trunk
    WHERE 
        (CURRENT_DATE BETWEEN start_date AND stop_date)
        OR (start_date <= CURRENT_DATE AND stop_date IS NULL)
        AND ip_addr IS NOT NULL
    ORDER BY code_trunk, start_date DESC
),
service_trunks AS (
    SELECT 
        st.id,
        st.trunk_id,
        ti.ip_addr
    FROM billing.service_trunk AS st
    LEFT JOIN trunk_ip AS ti
      ON st.trunk_id = ti.code_trunk
)
SELECT
    t.trunk_node_link_id,
    t.service_trunk_id,
    t.node_id,
    t.comment,
    CONCAT(n.node_name_id, ' - ', n.node_id) AS node_display,
    COALESCE(cct.name, 'Не задан') AS contract_type_text,
    t.contract_type_id,
    st.trunk_id       AS trunk_id,
    serv.ip_addr      AS ip_address,
    tr.name           AS phys_trunk_name,
    st.client_account_id,
    st.description    AS description,
    c.contragent_name AS contragent_name
FROM calligrapher.trunk_node_link AS t
LEFT JOIN calligrapher.node             AS n   ON n.node_id = t.node_id
LEFT JOIN billing.service_trunk         AS st  ON st.id      = t.service_trunk_id
LEFT JOIN stat.client_contract_type     AS cct ON cct.id     = st.contract_type_id
LEFT JOIN billing.clients               AS c   ON c.id       = st.client_account_id
LEFT JOIN service_trunks                AS serv ON serv.id  = t.service_trunk_id
LEFT JOIN auth.trunk                    AS tr  ON tr.id      = st.trunk_id
ORDER BY t.trunk_node_link_id
SQL;

        return Yii::$app->db
            ->createCommand($sql)
            ->queryAll();
    }

    /**
     * Возвращает одну запись связи транка по ID, с ip_address из copm.trunk.
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

        $sql = <<<SQL
WITH trunk_ip AS (
    SELECT DISTINCT ON (code_trunk) 
        code_trunk, ip_addr
    FROM copm.trunk
    WHERE 
        (CURRENT_DATE BETWEEN start_date AND stop_date)
        OR (start_date <= CURRENT_DATE AND stop_date IS NULL)
        AND ip_addr IS NOT NULL
    ORDER BY code_trunk, start_date DESC
),
service_trunks AS (
    SELECT 
        st.id,
        st.trunk_id,
        ti.ip_addr
    FROM billing.service_trunk AS st
    LEFT JOIN trunk_ip AS ti
      ON st.trunk_id = ti.code_trunk
)
SELECT
    t.*,
    st.trunk_id   AS trunk_id,
    serv.ip_addr  AS ip_address
FROM calligrapher.trunk_node_link AS t
LEFT JOIN billing.service_trunk AS st    ON st.id     = t.service_trunk_id
LEFT JOIN service_trunks        AS serv  ON serv.id   = t.service_trunk_id
WHERE t.trunk_node_link_id = :id
SQL;

        $link = Yii::$app->db
            ->createCommand($sql, [':id' => $id])
            ->queryOne();

        if ($link === false) {
            throw new HttpException(404, 'Связь транка не найдена');
        }

        return $link;
    }

    /**
     * Создание или обновление связи транка.
     *
     * @return array
     * @throws HttpException
     */
    public function actionSave()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $postData = Yii::$app->request->post();

        if (!empty($postData['trunk_node_link_id'])) {
            $model = TrunkNodeLink::findOne($postData['trunk_node_link_id']);
            if (!$model) {
                throw new HttpException(404, 'Связь транка не найдена');
            }
        } else {
            $model = new TrunkNodeLink();
        }

        $model->load($postData, '');
        if ($model->save()) {
            return [
                'success'            => true,
                'trunk_node_link_id' => $model->trunk_node_link_id,
                'message'            => 'Связь транка успешно сохранена',
            ];
        }

        return [
            'success' => false,
            'errors'  => $model->errors,
        ];
    }
}
