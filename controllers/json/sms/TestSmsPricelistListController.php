<?php

namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\auth\TestSmsPricelist as TestPricelist;
use yii\db\Expression;
use yii\web\HttpException;
use yii\db\Query;
use Yii;

class TestSmsPricelistListController extends JsonController
{
    protected $modelName     = TestPricelist::class;
    protected $idParamName   = 'id';
    protected $nameParamName = 'name';
    protected $readWhere     = ['server_id'];

    /** Список для выпадашки */
    public function actionList()
    {
        return TestPricelist::find()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->asArray()
            ->all();
    }

    /** Основной грид */
    public function actionRead()
    {
        $searchArray = $this->request['search_array'] ?? [];
        $limit       = $this->request['limit'] ?? 50;
        $offset      = $this->request['offset'] ?? 0;

        // фиксированный сервер
        $forcedServerId = 9;

        // фильтр по результату
        $testResult = $searchArray['result'] ?? false;
        switch ($testResult) {
            case 'not_executed':
                $resultWhere = "is_autotest AND (tr_last.passed IS null OR now() AT TIME ZONE 'UTC' - tr_last.tm::timestamp > INTERVAL '1 HOUR')";
                break;
            case 'passed':
                $resultWhere = "is_autotest AND tr_last.passed = true AND now() AT TIME ZONE 'UTC' - tr_last.tm::timestamp <= INTERVAL '1 HOUR'";
                break;
            case 'failed':
                $resultWhere = "is_autotest AND tr_last.passed = false AND now() AT TIME ZONE 'UTC' - tr_last.tm::timestamp <= INTERVAL '1 HOUR'";
                break;
            default:
                $resultWhere = 'true';
        }

        $trAlias = 'tr_last';

        // подзапрос с последним результатом
        $lastResultSubquery = (new Query())
            ->select(['id_pricelist', 'passed', 'expected', 'received', 'tm'])
            ->from('auth.a2p_test_result')
            ->where(['type' => 'pricelist', 'server_id' => $forcedServerId])
            ->orderBy(['id_pricelist' => SORT_ASC, 'tm' => SORT_DESC])
            ->distinct(true);

        $query = TestPricelist::find()
            ->alias('tp')
            ->select([
                'tp.*',
                'mcc.country AS mcc_name',
                'mnc.network AS mnc_name',
                'p.name AS pricelist_name',
                'p.service_type_id AS service_type_id',

                "{$trAlias}.tm AS last_tm",
                "{$trAlias}.passed AS last_passed",
                "{$trAlias}.expected AS last_expected",
                "{$trAlias}.received AS last_received",

                new Expression("
                    CASE
                        WHEN {$trAlias}.tm IS NULL THEN 'not_executed'
                        WHEN now() AT TIME ZONE 'UTC' - {$trAlias}.tm::timestamp <= INTERVAL '1 HOUR' AND {$trAlias}.passed = true  THEN 'passed'
                        WHEN now() AT TIME ZONE 'UTC' - {$trAlias}.tm::timestamp <= INTERVAL '1 HOUR' AND {$trAlias}.passed = false THEN 'failed'
                        ELSE 'not_executed'
                    END AS result
                "),
                new Expression("'#{$forcedServerId}: ' || COALESCE(s.name, 'SMS') AS server_name"),

                new Expression("
                    CASE 
                        WHEN tp.location_id = 1 THEN 'Домашний регион'
                        WHEN tp.location_id = 2 THEN 'Гостевой регион'
                        WHEN tp.location_id = 3 THEN 'Международный регион'
                    END AS location_name
                "),
            ])
            ->leftJoin([$trAlias => $lastResultSubquery], "{$trAlias}.id_pricelist = tp.id")
            ->leftJoin('nnp.mcc mcc', 'mcc.mcc = tp.mcc::text')
            ->leftJoin('nnp.mnc mnc', 'mnc.mnc = tp.mnc::text AND mnc.mcc = tp.mcc::text')
            ->leftJoin('billing_uu.pricelist p', 'p.id = tp.pricelist_id')
            ->leftJoin('public.server s', 's.id = tp.server_id')
            ->andWhere(['tp.server_id' => $forcedServerId])
            ->andWhere($resultWhere)
            ->orderBy('tp.name')
            ->limit($limit)
            ->offset($offset)
            ->asArray();

        // фильтры
        if (!empty($searchArray['group_id'])) {
            $query->andWhere(['tp.test_pricelist_group_id' => $searchArray['group_id']]);
        }
        if (!empty($searchArray['name'])) {
            $query->andWhere('tp.name ILIKE :name', [':name' => '%' . $searchArray['name'] . '%']);
        }
        if (!empty($searchArray['pricelist_id'])) {
            $query->andWhere('tp.pricelist_id = :pid', [':pid' => $searchArray['pricelist_id']]);
        }
        if (!empty($searchArray['id'])) {
            $query->andWhere('tp.id = :id', [':id' => $searchArray['id']]);
        }

        // totalCount (через clone безопасно)
        $countQuery = clone $query;
        $countQuery->select(new Expression('COUNT(*)'));
        $countQuery->limit(-1)->offset(-1)->orderBy([]);

        $totalCount = Yii::$app->db->createCommand($countQuery->createCommand()->rawSql)->queryScalar();

        return [
            'totalCount' => (int)$totalCount,
            'data'       => $query->all(),
        ];
    }

    /** Чтение одного элемента */
    public function actionGet()
    {
        $item = TestPricelist::find()
            ->alias('tp')
            ->leftJoin('billing_uu.pricelist p', 'p.id = tp.pricelist_id')
            ->select([
                'tp.*',
                'tr.tm',
                'tr.received',
                'p.service_type_id AS pricelist_service_type_id',
                'p.orig AS pricelist_orig',
            ])
            ->leftJoin('auth.a2p_test_result tr', "tr.type = 'pricelist' AND tr.id_pricelist = tp.id")
            ->where(['tp.id' => $this->request['id']])
            ->asArray()
            ->one();

        if ($item === null) {
            throw new HttpException(404, 'TestSmsPricelist not found');
        }

        return $item;
    }

    /** Сохранение (create/update) */
    public function actionSave()
    {
        if (isset($this->request['id'])) {
            $item = $this->getTestPricelistOr404($this->request['id']);
        } else {
            $item = TestPricelist::create();
        }

        $data = $this->request;
        $data['server_id'] = 9;
        unset($data['service_type_id'], $data['pricelist_service_type_id']);
        $item->load($data, '');

        if (!$item->save()) {
            throw new FormValidationException($item);
        }

        $svcType = (new Query())
            ->select('service_type_id')
            ->from('billing_uu.pricelist')
            ->where(['id' => $item->pricelist_id])
            ->scalar();

        return [
            'success'          => 1,
            'id'               => $item->id,
            'service_type_id'  => (int)$svcType,
        ];
    }

    /** Удаление */
    public function actionDelete()
    {
        $item = TestPricelist::findOne($this->request['id']);
        $item->delete();
    }

    /** Результат теста */
    public function actionResult()
    {
        $id   = $this->request['id'];
        $item = TestPricelist::findOne($id);
        if (!$item) {
            throw new HttpException(404, 'TestSmsPricelist not found');
        }

        $pl = (new Query())
            ->select(['service_type_id', 'orig'])
            ->from('billing_uu.pricelist')
            ->where(['id' => $item->pricelist_id])
            ->one();

        $serviceTypeId      = (int)($pl['service_type_id'] ?? 0);
        $isOrigByPricelist  = !empty($pl['orig']);

        if ($serviceTypeId === 2) {
            $params = [
                'num_a'        => $item->a_number,
                'num_b'        => $item->b_number,
                'location_id'  => $item->location_id,
                'pricelist_id' => $item->pricelist_id,
                'is_orig'      => $isOrigByPricelist ? 'true' : 'false',
            ];

            $reg99Base = Yii::$app->params['isEuropean']
                ? 'http://10.250.30.48:8103/nnpcalc'
                : 'http://reg99.mcntelecom.ru:8103/nnpcalc';

            $url = $reg99Base . '?' . http_build_query($params);
            $raw = @file_get_contents($url);
            $data = json_decode($raw, true) ?: [];

            return [
                'steps'    => [$data],
                'a_number' => $item->a_number,
                'b_number' => $item->b_number,
                'c_number' => $item->c_number,
                'id'       => $item->id,
                'name'     => $item->name,
                'url'      => $url,
                'baseUrl'  => Yii::$app->params['isEuropean']
                    ? 'https://voipgui.kompaas.tech/'
                    : 'https://voipgui.mcn.ru/',
            ];
        }

        $apiUrl = $item->server->apiUrl;
        $apiParams = [
            'cmd'          => 'priceV2Calc',
            'num_a'        => $item->a_number,
            'num_b'        => $item->b_number,
            'num_c'        => $item->c_number,
            'mcc'          => $item->mcc,
            'mnc'          => $item->mnc,
            'location_id'  => $item->location_id,
            'pricelist_id' => $item->pricelist_id,
            'orig'         => $isOrigByPricelist ? 'true' : 'false',
            'test_mode'    => 'true',
            'date'         => $item->mock_current_date,
        ];

        if ($item->with_debug_info) $apiParams['with_debug_info'] = 1;
        if ($item->sim_partner_id) $apiParams['sim_partner_id'] = $item->sim_partner_id;
        if ($item->sim_profile_id) $apiParams['sim_profile_id'] = $item->sim_profile_id;

        $requestUrl = $apiUrl . 'test/nnpcalc?' . http_build_query($apiParams);
        $response   = @file_get_contents($requestUrl);

        return [
            'steps'    => [json_decode($response, true)],
            'a_number' => $item->a_number,
            'b_number' => $item->b_number,
            'c_number' => $item->c_number,
            'id'       => $item->id,
            'name'     => $item->name,
            'url'      => $requestUrl,
            'baseUrl'  => Yii::$app->params['isEuropean']
                ? 'https://voipgui.kompaas.tech/'
                : 'https://voipgui.mcn.ru/',
        ];
    }

    protected function getTestPricelistOr404($id)
    {
        $item = TestPricelist::findOne($id);
        if (!$item) {
            throw new HttpException(404, 'TestSmsPricelist not found');
        }
        return $item;
    }
}
