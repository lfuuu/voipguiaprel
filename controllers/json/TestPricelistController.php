<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\auth\TestPricelist;
use app\models\Server;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use Yii;

class TestPricelistController extends JsonController
{
    
    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws HttpException
     */
    public function actionList()
    {
        if (!\Yii::$app->user->can('test_pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
                
        return
            TestPricelist::find()
                ->select(['id', 'name'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws HttpException
     */
   public function actionRead()
    {
        if (!\Yii::$app->user->can('test_pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $searchArray = $this->request['search_array'];
        $limit       = $this->request['limit'];
        $offset      = $this->request['offset'];

        // Построение условия по результатам
        $testResult = $searchArray['result'] ?? false;
        switch ($testResult) {
            case 'not_executed':
                $resultWhere = 'is_autotest AND (tr.passed IS null OR now() AT TIME ZONE \'UTC\' - tr.tm::timestamp > INTERVAL \'1 HOUR\')';
                break;
            case 'passed':
                $resultWhere = 'is_autotest AND tr.passed = true AND now() AT TIME ZONE \'UTC\' - tr.tm::timestamp <= INTERVAL \'1 HOUR\'';
                break;
            case 'failed':
                $resultWhere = 'is_autotest AND tr.passed = false AND now() AT TIME ZONE \'UTC\' - tr.tm::timestamp <= INTERVAL \'1 HOUR\'';
                break;
            default:
                $resultWhere = 'true';
        }

        // Основной запрос
        $query = TestPricelist::find()
            ->alias('tp')
            ->select([
                'tp.*',
                'mcc.country     AS mcc_name',
                'mnc.network     AS mnc_name',
                'p.name          AS pricelist_name',
                'p.type_id       AS type_id',           // <-- вот это добавлено
                new Expression("
                    CASE 
                        WHEN tp.location_id = 1 THEN 'Домашний регион'
                        WHEN tp.location_id = 2 THEN 'Гостевой регион'
                        WHEN tp.location_id = 3 THEN 'Международный регион'
                    END AS location_name
                "),
                new Expression("
                    CASE 
                        WHEN tr.passed IS null OR now() AT TIME ZONE 'UTC' - tr.tm::timestamp > INTERVAL '1 HOUR' 
                            THEN 'not_executed' 
                        WHEN tr.passed = true THEN 'passed' 
                        WHEN tr.passed = false THEN 'failed' 
                    END AS result
                "),
                new Expression("'#' || s.id || ': ' || s.name AS server_name"),
            ])
            ->leftJoin('auth.test_result tr', 'tr.type = \'pricelist\' AND tr.id_pricelist = tp.id')
            ->leftJoin('nnp.mcc      mcc', 'mcc.mcc = tp.mcc::text')
            ->leftJoin('nnp.mnc      mnc', 'mnc.mnc = tp.mnc::text AND mnc.mcc = tp.mcc::text')
            ->leftJoin('billing_uu.pricelist p', 'p.id = tp.pricelist_id')
            ->innerJoin('public.server      s', 's.id = tp.server_id')
            ->andWhere($resultWhere)
            ->orderBy('tp.name')
            ->limit($limit)
            ->offset($offset)
            ->asArray();

        // Копия для подсчёта
        $countQuery = TestPricelist::find()
            ->alias('tp')
            ->leftJoin('auth.test_result tr', 'tr.type = \'pricelist\' AND tr.id_pricelist = tp.id')
            ->andWhere($resultWhere);

        // Применяем фильтры из $searchArray...
        if (!empty($searchArray['group_id'])) {
            $query     ->andWhere(['tp.test_pricelist_group_id' => $searchArray['group_id']]);
            $countQuery->andWhere(['tp.test_pricelist_group_id' => $searchArray['group_id']]);
        }
        if (!empty($searchArray['name'])) {
            $query     ->andWhere('tp.name ILIKE :name')->addParams([':name' => '%'.$searchArray['name'].'%']);
            $countQuery->andWhere('tp.name ILIKE :name')->addParams([':name' => '%'.$searchArray['name'].'%']);
        }
        if (!empty($searchArray['server_id'])) {
            $query     ->andWhere('tp.server_id = :sid')->addParams([':sid' => $searchArray['server_id']]);
            $countQuery->andWhere('tp.server_id = :sid')->addParams([':sid' => $searchArray['server_id']]);
        }
        if (!empty($searchArray['pricelist_id'])) {
            $query     ->andWhere('tp.pricelist_id = :pid')->addParams([':pid' => $searchArray['pricelist_id']]);
            $countQuery->andWhere('tp.pricelist_id = :pid')->addParams([':pid' => $searchArray['pricelist_id']]);
        }
        if (!empty($searchArray['id'])) {
            $query     ->andWhere('tp.id = :id')->addParams([':id' => $searchArray['id']]);
            $countQuery->andWhere('tp.id = :id')->addParams([':id' => $searchArray['id']]);
        }

        $data  = $query->all();
        $count = $countQuery->count();

        return [
            'totalCount' => $count,
            'data'       => $data,
        ];
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionGet()
{
    $item = TestPricelist::find()
        ->alias('tp')
        // 1) Добавляем JOIN на таблицу pricelist, чтобы достать её type_id
        ->leftJoin('billing_uu.pricelist p', 'p.id = tp.pricelist_id')
        // 2) В выборке указываем p.type_id как отдельное поле
        ->select([
            'tp.*',
            'p.type_id AS pricelist_type_id',
            'tr.tm',
            'tr.received',
        ])
        ->leftJoin(
            'auth.test_result tr',
            'tr.type = \'pricelist\' AND tr.id_pricelist = tp.id'
        )
        ->where(['tp.id' => $this->request['id']])
        ->asArray()
        ->one();

    if ($item === null) {
        throw new HttpException(404, 'TestPricelist не найден');
    }

    return $item;
}

    /**
     * @throws FormValidationException
     * @throws HttpException
     * @throws \yii\db\Exception
     */
public function actionSave()
    {
        if (isset($this->request['id'])) {
            $item = $this->getTestPricelistOr404($this->request['id']);
        } else {
            $item = TestPricelist::create();
        }

        $item->load($this->request, '');

        if (!$item->save()) {
            throw new FormValidationException($item);
        }

        // возвращаем и type_id сюда, чтобы фронт мог подхватить
        return [
            'success' => 1,
            'id'      => $item->id,
            'type_id' => $item->type_id,
        ];
    }

    /**
     * @inheritdoc
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('test_pricelist_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = TestPricelist::findOne($this->request['id']);
        $item->delete();
    }
    
    /**
     * @return array
     * @throws HttpException
     */
    
    /**
     * Новый actionResult: если type_id==2, зовём внешний reg99-сервис,
     * иначе – прежняя логика через priceV2Calc.
     */
    public function actionResult()
    {
        $id   = $this->request['id'];
        $item = TestPricelist::findOne($id);
        if (!$item) {
            throw new HttpException(404, 'TestPricelist не найден');
        }

        // Вытащим type_id из таблицы pricelist
        $typeId = (new \yii\db\Query())
            ->select('type_id')
            ->from('billing_uu.pricelist')
            ->where(['id' => $item->pricelist_id])
            ->scalar();

        // Ветка для type_id == 2 (Reg99)
        if ((int)$typeId === 2) {
            $params = [
                'num_a'        => $item->a_number,
                'num_b'        => $item->b_number,
                'location_id'  => $item->location_id,
                'pricelist_id' => $item->pricelist_id,
            ];
            $url      = 'http://reg99.mcntelecom.ru:8101/nnpcalc?' . http_build_query($params);
            Yii::info("Reg99 NNPCalc URL: $url", __METHOD__);

            $response = @file_get_contents($url);
            if ($response === false) {
                throw new HttpException(502, 'Не удалось получить данные от reg99-сервиса');
            }
            Yii::info("Reg99 NNPCalc response: $response", __METHOD__);

            $raw = json_decode($response, true);

            // Соберём корневой узел для дерева
            $root = [
                'name'               => $raw['trace']['name']              ?? null,
                'steps'              => $raw['trace']['nodes']             ?? [],
                'match'              => $raw['match']                       ?? null,
                'fix_price'          => $raw['fix_price']                   ?? null,
                'rate_price'         => $raw['rate_price']                  ?? null,
                'interconnect_price' => $raw['interconnect_price']          ?? null,
            ];

            return [
                'steps'          => [ $root ],
                'number_range_a' => $this->getNumberRangeByNum($item->a_number, $item->server->apiUrl),
                'number_range_b' => $this->getNumberRangeByNum($item->b_number, $item->server->apiUrl),
                'number_range_c' => $this->getNumberRangeByNum($item->c_number, $item->server->apiUrl),
                'destination_a'  => $this->getDestinationByNum($item->a_number, $item->server->apiUrl),
                'destination_b'  => $this->getDestinationByNum($item->b_number, $item->server->apiUrl),
                'destination_c'  => $this->getDestinationByNum($item->c_number, $item->server->apiUrl),
                'a_number'       => $item->a_number,
                'b_number'       => $item->b_number,
                'c_number'       => $item->c_number,
                'id'             => $item->id,
                'name'           => $item->name,
                'url'            => $url,
                'baseUrl'        => Yii::$app->params['isEuropean']
                                      ? 'https://voipgui.kompaas.tech/'
                                      : 'https://voipgui.mcn.ru/',
            ];
        }

        // Иначе — старая логика через priceV2Calc
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
            'orig'         => $item->is_orig ? 'true' : 'false',
            'test_mode'    => 'true',
            'date'         => $item->mock_current_date,
        ];
        if ($item->with_debug_info)    { $apiParams['with_debug_info']   = 1; }
        if ($item->sim_partner_id)     { $apiParams['sim_partner_id']    = $item->sim_partner_id; }
        if ($item->sim_profile_id)     { $apiParams['sim_profile_id']    = $item->sim_profile_id; }

        $requestUrl = $apiUrl . 'test/nnpcalc?' . http_build_query($apiParams);
        Yii::info("PriceV2Calc URL: $requestUrl", __METHOD__);

        $response = @file_get_contents($requestUrl);
        if ($response === false) {
            throw new HttpException(502, 'Внешний NNPCalc недоступен');
        }

        return [
            'steps'          => [ json_decode($response, true) ],
            'number_range_a' => $this->getNumberRangeByNum($item->a_number, $apiUrl),
            'number_range_b' => $this->getNumberRangeByNum($item->b_number, $apiUrl),
            'number_range_c' => $this->getNumberRangeByNum($item->c_number, $apiUrl),
            'destination_a'  => $this->getDestinationByNum($item->a_number, $apiUrl),
            'destination_b'  => $this->getDestinationByNum($item->b_number, $apiUrl),
            'destination_c'  => $this->getDestinationByNum($item->c_number, $apiUrl),
            'a_number'       => $item->a_number,
            'b_number'       => $item->b_number,
            'c_number'       => $item->c_number,
            'id'             => $item->id,
            'name'           => $item->name,
            'url'            => $requestUrl,
            'baseUrl'        => Yii::$app->params['isEuropean']
                                  ? 'https://voipgui.kompaas.tech/'
                                  : 'https://voipgui.mcn.ru/',
        ];
    }

    
    /**
     * @return array
     * @throws HttpException
     */
    public function actionNumberResult()
    {
        if (!\Yii::$app->user->can('test_pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $number = preg_replace('~\D~', '', $this->request['number']);
        
        $apiUrl = Yii::$app->params['testNumberLink'];
        
        return [
            'number' => $number,
            'number_range' => $this->getNumberRangeByNum($number, $apiUrl),
            'destination' => $this->getDestinationByNum($number, $apiUrl),
        ];
    }
    
    private function getNumberRangeByNum($number, $apiUrl)
    {
        $fields = [
            'nnp_city_id' => 'app\models\nnp\City',
            'ndc_type_id' => 'app\models\nnp\NdcType',
            'nnp_operator_id' => 'app\models\nnp\Operator',
            'nnp_region_id' => 'app\models\nnp\Region',
            'ported_operator_id' => 'app\models\nnp\Operator',
        ];
        
        return $this->getCmdByNum('getNumberRangeByNum', $number, $apiUrl, $fields);
    }
    
    private function getDestinationByNum($number, $apiUrl)
    {
        return $this->getCmdByNum('getDestinationByNum', $number, $apiUrl);
    }
    
    private function getCmdByNum($cmd, $number, $apiUrl, $fields = [])
    {

        if ($cmd === 'getNumberRangeByNum') {
            $apiUrl = 'https://api-gw.mcn.ru/voipbilld/reg/';
        }

        $apiParams = [
            'cmd' => $cmd,
            'num' => $number,
        ];


        $request = $apiUrl . 'test/nnpcalc?' . http_build_query($apiParams);
        $response = @file_get_contents($request);

        if ($response === false) {
            $fallbackUrl = 'https://api-gw.kompaas.tech/voipbilld/reg/';
            $request = $fallbackUrl . 'test/nnpcalc?' . http_build_query($apiParams);
            $response = @file_get_contents($request);
        }

        $result = json_decode($response, true);
        if (!is_array($result)) {

            $result = [];
        }

        if (!empty($fields) && !empty($result)) {
            foreach ($result as $key => $value) {
                if (array_key_exists($key, $fields)) {
                    $newItem = $fields[$key]::findOne(['id' => $value]);
                    if ($newItem) {
                        $result[$key . '_name'] = $newItem->name;
                    }
                }
            }
        }

        ksort($result);

        return $result;
    }
    

}
