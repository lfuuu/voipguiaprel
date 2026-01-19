<?php
/* app/controllers/json/PricelistLocationController.php */
namespace app\controllers\json;

use app\models\billing_uu\PricelistLocation;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\db\StaleObjectException;

class PricelistLocationController extends JsonController
{
    public function actionGet()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        return PricelistLocation::find()
            ->where(['id' => $this->request['id']])
            ->asArray()
            ->one();
    }

    public function actionListByPricelist()
    {
        if (!\Yii::$app->user->can('pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $pricelistId = $this->request['pricelist_id'];

        return PricelistLocation::find()
            ->select(['id' => 'id', 'name' => 'id'])
            ->where(['pricelist_id' => $pricelistId])
            ->asArray()
            ->all();
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('pricelist_edit') && !\Yii::$app->user->can('pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $result = [];

        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('pricelist_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            $item = $this->getPricelistLocationOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('pricelist_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            $item = PricelistLocation::create();
            $result['log'] = ['data_before' => []];
        }

        $item->load($this->request, '');

        $transaction = PricelistLocation::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }
            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }

        $result['log']['data_after'] = $this->getDataForLog($item);
        return $result;
    }

    /**
     * Массовый импорт записей в billing_uu.pricelist_location
     * Вход:
     *  - pricelist_id (int)
     *  - location_id (int)
     *  - sim_partner (int[])
     *  - sim_profile (int[])
     *  - rounding_threshold (int, optional)
     *  - rows: [{mcc:int,mnc:int,delta_price:string,description:string}]
     */
    public function actionBulkImport()
{
    if (!\Yii::$app->user->can('pricelist_create') && !\Yii::$app->user->can('pricelist_edit')) {
        throw new ForbiddenHttpException('Access denied');
    }

    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    $req = $this->request;

    $pricelistId = (int)($req['pricelist_id'] ?? 0);
    $locationId  = (int)($req['location_id']  ?? 0);
    $simPartner  = $req['sim_partner'] ?? [];
    $simProfile  = $req['sim_profile'] ?? [];
    $rounding    = isset($req['rounding_threshold']) ? (int)$req['rounding_threshold'] : 0;
    $rows        = $req['rows'] ?? [];
    $replace     = !empty($req['replace']); // <<< НОВОЕ

    if (!$pricelistId || !$locationId) {
        throw new HttpException(400, 'pricelist_id и location_id обязательны');
    }
    if (empty($rows) || !is_array($rows)) {
        throw new HttpException(400, 'Нет данных для импорта (rows)');
    }

    // Нормализация общих массивов
    $simPartner = array_values(array_filter(array_map('intval', (array)$simPartner), fn($v) => $v > 0));
    $simProfile = array_values(array_filter(array_map('intval', (array)$simProfile), fn($v) => $v > 0));
    sort($simPartner); sort($simProfile);

    // Лёгкая валидация строк
    $validRows = [];
    $errors    = [];
    $i = 0;

    foreach ($rows as $r) {
        $i++;
        $mcc   = isset($r['mcc']) ? (int)$r['mcc'] : 0;
        $mnc   = (isset($r['mnc']) && $r['mnc'] !== '') ? (int)$r['mnc'] : 0;
        $delta = isset($r['delta_price']) ? (string)$r['delta_price'] : '';
        $descr = isset($r['description']) ? (string)$r['description'] : '';

        $bad = [];
        if ($mcc <= 0) $bad[] = 'MCC';
        if ($mnc < 0)  $bad[] = 'MNC';
        if ($delta === '' || !preg_match('/^-?\d{1,4}(\.\d{1,6})?$/', $delta)) $bad[] = 'delta_price';

        if ($bad) {
            $errors[] = ['row' => $i, 'message' => 'Неверные поля: ' . implode(', ', $bad)];
            continue;
        }

        $validRows[] = [
            'mcc'         => $mcc,
            'mnc'         => $mnc,
            'delta_price' => $delta,
            'description' => $descr
        ];
    }

    $db = PricelistLocation::getDb();
    $tx = $db->beginTransaction();

    $inserted = 0;
    $updated  = 0;
    $deleted  = 0;

    try {
        if ($replace) {
            // Полная замена: удалить все записи для (pricelist_id, location_id)
            $deleted = (int)$db->createCommand("
                DELETE FROM billing_uu.pricelist_location
                 WHERE pricelist_id = :pl AND location_id = :loc
            ", [':pl' => $pricelistId, ':loc' => $locationId])->execute();

            if (!empty($validRows)) {
                $sqlInsert = "
                    WITH src AS (
                      SELECT mcc,
                             mnc,
                             delta_price::numeric(10,6) AS delta_price,
                             coalesce(description,'')::text AS description
                      FROM json_to_recordset(:rows::json) AS t(
                        mcc int, mnc int, delta_price text, description text
                      )
                    )
                    INSERT INTO billing_uu.pricelist_location
                      (pricelist_id, location_id, mcc, mnc, delta_price, description, rounding_threshold, sim_partner, sim_profile)
                    SELECT
                      :pl, :loc, ARRAY[mcc]::integer[], ARRAY[mnc]::integer[],
                      delta_price, description, :rt, :sp::integer[], :spf::integer[]
                    FROM src
                ";
                $inserted = (int)$db->createCommand($sqlInsert, [
                    ':rows' => json_encode($validRows, JSON_UNESCAPED_UNICODE),
                    ':pl'   => $pricelistId,
                    ':loc'  => $locationId,
                    ':rt'   => $rounding,
                    ':sp'   => '{' . implode(',', $simPartner) . '}',
                    ':spf'  => '{' . implode(',', $simProfile) . '}',
                ])->execute();
            }
        } else {
            // UPSERT: сначала обновим совпавшие по ключу (pl, loc, mcc, mnc), затем вставим отсутствующие
            if (!empty($validRows)) {
                // UPDATE
                $sqlUpdate = "
                    WITH src AS (
                      SELECT mcc,
                             mnc,
                             delta_price::numeric(10,6) AS delta_price,
                             coalesce(description,'')::text AS description
                      FROM json_to_recordset(:rows::json) AS t(
                        mcc int, mnc int, delta_price text, description text
                      )
                    )
                    UPDATE billing_uu.pricelist_location t
                       SET delta_price       = src.delta_price,
                           description       = src.description,
                           rounding_threshold= :rt,
                           sim_partner       = :sp::integer[],
                           sim_profile       = :spf::integer[]
                      FROM src
                     WHERE t.pricelist_id = :pl
                       AND t.location_id  = :loc
                       AND t.mcc = ARRAY[src.mcc]::integer[]
                       AND t.mnc = ARRAY[src.mnc]::integer[]
                ";
                $updated = (int)$db->createCommand($sqlUpdate, [
                    ':rows' => json_encode($validRows, JSON_UNESCAPED_UNICODE),
                    ':pl'   => $pricelistId,
                    ':loc'  => $locationId,
                    ':rt'   => $rounding,
                    ':sp'   => '{' . implode(',', $simPartner) . '}',
                    ':spf'  => '{' . implode(',', $simProfile) . '}',
                ])->execute();

                // INSERT МИССИНГОВ
                $sqlInsertMissing = "
                    WITH src AS (
                      SELECT mcc,
                             mnc,
                             delta_price::numeric(10,6) AS delta_price,
                             coalesce(description,'')::text AS description
                      FROM json_to_recordset(:rows::json) AS t(
                        mcc int, mnc int, delta_price text, description text
                      )
                    ),
                    miss AS (
                      SELECT src.*
                        FROM src
                        LEFT JOIN billing_uu.pricelist_location t
                          ON t.pricelist_id = :pl
                         AND t.location_id  = :loc
                         AND t.mcc = ARRAY[src.mcc]::integer[]
                         AND t.mnc = ARRAY[src.mnc]::integer[]
                       WHERE t.id IS NULL
                    )
                    INSERT INTO billing_uu.pricelist_location
                      (pricelist_id, location_id, mcc, mnc, delta_price, description, rounding_threshold, sim_partner,  sim_profile)
                    SELECT
                      :pl, :loc, ARRAY[mcc]::integer[], ARRAY[mnc]::integer[],
                      delta_price, description, :rt, :sp::integer[], :spf::integer[]
                    FROM miss
                ";
                $inserted = (int)$db->createCommand($sqlInsertMissing, [
                    ':rows' => json_encode($validRows, JSON_UNESCAPED_UNICODE),
                    ':pl'   => $pricelistId,
                    ':loc'  => $locationId,
                    ':rt'   => $rounding,
                    ':sp'   => '{' . implode(',', $simPartner) . '}',
                    ':spf'  => '{' . implode(',', $simProfile) . '}',
                ])->execute();
            }
        }

        $tx->commit();
    } catch (\Throwable $e) {
        if ($tx->isActive) $tx->rollBack();
        throw new HttpException(500, 'Ошибка импорта: ' . $e->getMessage());
    }

    $result = [
        'mode'     => $replace ? 'replace' : 'upsert',
        'inserted' => $inserted,
        'updated'  => $updated,
        'deleted'  => $deleted,
        'failed'   => count($errors),
        'errors'   => $errors
    ];
    if (empty($errors)) {
        unset($result['errors']);
    }

    return $result;
}

    /**
     * @throws StaleObjectException
     * @throws HttpException
     * @throws \Exception
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('pricelist_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        $item = $this->getPricelistLocationOr404($this->request['id']);
        $item->delete();
    }

    /* ===== Вспомогательные методы для самодостаточности файла ===== */

    /**
     * @param int $id
     * @return PricelistLocation
     * @throws HttpException
     */
    protected function getPricelistLocationOr404($id)
    {
        $item = PricelistLocation::findOne((int)$id);
        if (!$item) {
            throw new HttpException(404, 'PricelistLocation not found');
        }
        return $item;
    }

    /**
     * Упрощённые данные для лога изменений
     */
    protected function getDataForLog(PricelistLocation $item): array
    {
        // Если у модели есть своя toArray — этого достаточно.
        return $item->toArray();
    }
}
