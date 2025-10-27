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
use yii\db\Query;

class TestPricelistController extends JsonController
{
    protected $modelName        = TestPricelist::class;
    protected $idParamName      = 'id';
    protected $nameParamName    = 'name';
    protected $readWhere        = ['server_id'];
    protected $createPermission = 'test_pricelist_create';
    protected $listPermission   = 'test_pricelist_list';
    protected $editPermission   = 'test_pricelist_edit';
    protected $deletePermission = 'test_pricelist_delete';

    /**
     * Список для выпадашки
     */
    public function actionList()
    {
        if (!Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }
        return TestPricelist::find()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->asArray()
            ->all();
    }

    /**
     * Основной грид
     */
    public function actionRead()
    {
        if (!Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $searchArray = $this->request['search_array'] ?? [];
        $limit       = $this->request['limit'];
        $offset      = $this->request['offset'];

        // Фильтр по passed/not_executed/passed/failed
        $testResult = $searchArray['result'] ?? false;
        switch ($testResult) {
            case 'not_executed':
                $resultWhere = "is_autotest AND (tr.passed IS null OR now() AT TIME ZONE 'UTC' - tr.tm::timestamp > INTERVAL '1 HOUR')";
                break;
            case 'passed':
                $resultWhere = "is_autotest AND tr.passed = true AND now() AT TIME ZONE 'UTC' - tr.tm::timestamp <= INTERVAL '1 HOUR'";
                break;
            case 'failed':
                $resultWhere = "is_autotest AND tr.passed = false AND now() AT TIME ZONE 'UTC' - tr.tm::timestamp <= INTERVAL '1 HOUR'";
                break;
            default:
                $resultWhere = 'true';
        }

        $query = TestPricelist::find()
            ->alias('tp')
            ->select([
                'tp.*',
                'mcc.country     AS mcc_name',
                'mnc.network     AS mnc_name',
                'p.name          AS pricelist_name',
                'p.service_type_id AS service_type_id', // <-- вытаскиваем нужное поле
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
            ->leftJoin('nnp.mcc mcc',       'mcc.mcc = tp.mcc::text')
            ->leftJoin('nnp.mnc mnc',       'mnc.mnc = tp.mnc::text AND mnc.mcc = tp.mcc::text')
            ->leftJoin('billing_uu.pricelist p', 'p.id = tp.pricelist_id')
            ->innerJoin('public.server s',      's.id = tp.server_id')
            ->andWhere($resultWhere)
            ->orderBy('tp.name')
            ->limit($limit)
            ->offset($offset)
            ->asArray();

        $countQuery = TestPricelist::find()
            ->alias('tp')
            ->leftJoin('auth.test_result tr', 'tr.type = \'pricelist\' AND tr.id_pricelist = tp.id')
            ->andWhere($resultWhere);

        // дополнительные фильтры
        if (!empty($searchArray['group_id'])) {
            $query     ->andWhere(['tp.test_pricelist_group_id' => $searchArray['group_id']]);
            $countQuery->andWhere(['tp.test_pricelist_group_id' => $searchArray['group_id']]);
        }
        if (!empty($searchArray['name'])) {
            $query     ->andWhere('tp.name ILIKE :name')->addParams([':name'=>'%'.$searchArray['name'].'%']);
            $countQuery->andWhere('tp.name ILIKE :name')->addParams([':name'=>'%'.$searchArray['name'].'%']);
        }
        if (!empty($searchArray['server_id'])) {
            $query     ->andWhere('tp.server_id = :sid')->addParams([':sid'=>$searchArray['server_id']]);
            $countQuery->andWhere('tp.server_id = :sid')->addParams([':sid'=>$searchArray['server_id']]);
        }
        if (!empty($searchArray['pricelist_id'])) {
            $query     ->andWhere('tp.pricelist_id = :pid')->addParams([':pid'=>$searchArray['pricelist_id']]);
            $countQuery->andWhere('tp.pricelist_id = :pid')->addParams([':pid'=>$searchArray['pricelist_id']]);
        }
        if (!empty($searchArray['id'])) {
            $query     ->andWhere('tp.id = :id')->addParams([':id'=>$searchArray['id']]);
            $countQuery->andWhere('tp.id = :id')->addParams([':id'=>$searchArray['id']]);
        }

        $data  = $query->all();
        $count = $countQuery->count();

        return [
            'totalCount' => $count,
            'data'       => $data,
        ];
    }

    /**
     * Чтение одного элемента (для редактирования)
     */
    public function actionGet()
    {
        if (!Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $item = TestPricelist::find()
            ->alias('tp')
            ->leftJoin('billing_uu.pricelist p', 'p.id = tp.pricelist_id')
            ->select([
                'tp.*',
                'tr.tm',
                'tr.received',
                'p.service_type_id AS pricelist_service_type_id', // <-- нужное поле
            ])
            ->leftJoin('auth.test_result tr', "tr.type = 'pricelist' AND tr.id_pricelist = tp.id")
            ->where(['tp.id' => $this->request['id']])
            ->asArray()
            ->one();

        if ($item === null) {
            throw new HttpException(404, 'TestPricelist не найден');
        }

        return $item;
    }

    /**
     * Сохранение (create/update)
     */
    public function actionSave()
    {
        if (isset($this->request['id'])) {
            $item = $this->getTestPricelistOr404($this->request['id']);
        } else {
            $item = TestPricelist::create();
        }

        // Загружаем только разрешённые поля
        $data = $this->request;
        unset($data['service_type_id'], $data['pricelist_service_type_id']); // чистим лишнее
        $item->load($data, '');

        if (!$item->save()) {
            throw new FormValidationException($item);
        }

        // После сохранения подхватим service_type_id из pricelist для фронта
        $svcType = (new Query())
            ->select('service_type_id')
            ->from('billing_uu.pricelist')
            ->where(['id' => $item->pricelist_id])
            ->scalar();

        return [
            'success'              => 1,
            'id'                   => $item->id,
            'service_type_id'      => (int)$svcType,
        ];
    }

    /**
     * Удаление
     */
    public function actionDelete()
    {
        if (!Yii::$app->user->can($this->deletePermission)) {
            throw new ForbiddenHttpException('Access denied');
        }
        $item = TestPricelist::findOne($this->request['id']);
        $item->delete();
    }

   public function actionResult()
{
    $id   = $this->request['id'];
    $item = TestPricelist::findOne($id);
    if (!$item) {
        throw new HttpException(404, 'TestPricelist не найден');
    }

    // узнаём service_type_id из pricelist
    $serviceTypeId = (new Query())
        ->select('service_type_id')
        ->from('billing_uu.pricelist')
        ->where(['id' => $item->pricelist_id])
        ->scalar();

    if ((int)$serviceTypeId === 2) {
        // ----- REG99 ветка: формируем URL и возвращаем ЕДИНУЮ структуру
        $params = [
            'num_a'        => $item->a_number,
            'num_b'        => $item->b_number,
            'location_id'  => $item->location_id,
            'pricelist_id' => $item->pricelist_id,
            'orig'      => $item->is_orig ? 'true' : 'false',
        ];
        $url = 'http://reg99.mcntelecom.ru:8101/nnpcalc?' . http_build_query($params);
        Yii::info("Reg99 NNPCalc URL: $url", __METHOD__);
        $raw = @file_get_contents($url);
        if ($raw === false) {
            throw new HttpException(502, 'Не удалось получить данные от reg99-сервиса');
        }
        $data = json_decode($raw, true) ?: [];

        return [
            'steps'           => [ $data ],
            'a_number'        => $item->a_number,
            'b_number'        => $item->b_number,
            'c_number'        => $item->c_number,
            'id'              => $item->id,
            'name'            => $item->name,
            'url'             => $url, // <<< теперь есть
            'baseUrl'         => Yii::$app->params['isEuropean']
                                   ? 'https://voipgui.kompaas.tech/'
                                   : 'https://voipgui.mcn.ru/',
        ];
    }

    // ----- PriceV2 ветка: как у вас, но структура та же (url уже есть)
    $apiUrl = $item->server->apiUrl;
    $apiParams = [
        'cmd'           => 'priceV2Calc',
        'num_a'         => $item->a_number,
        'num_b'         => $item->b_number,
        'num_c'         => $item->c_number,
        'mcc'           => $item->mcc,
        'mnc'           => $item->mnc,
        'location_id'   => $item->location_id,
        'pricelist_id'  => $item->pricelist_id,
        'orig'          => $item->is_orig ? 'true' : 'false',
        'test_mode'     => 'true',
        'date'          => $item->mock_current_date,
    ];
    if ($item->with_debug_info) $apiParams['with_debug_info'] = 1;
    if ($item->sim_partner_id) $apiParams['sim_partner_id'] = $item->sim_partner_id;
    if ($item->sim_profile_id) $apiParams['sim_profile_id'] = $item->sim_profile_id;

    $requestUrl = $apiUrl . 'test/nnpcalc?' . http_build_query($apiParams);
    Yii::info("PriceV2Calc URL: $requestUrl", __METHOD__);
    $response = @file_get_contents($requestUrl);
    if ($response === false) {
        throw new HttpException(502, 'Внешний NNPCalc недоступен');
    }

    return [
        'steps'           => [ json_decode($response, true) ],
        'number_range_a'  => $this->getNumberRangeByNum($item->a_number, $apiUrl),
        'number_range_b'  => $this->getNumberRangeByNum($item->b_number, $apiUrl),
        'number_range_c'  => $this->getNumberRangeByNum($item->c_number, $apiUrl),
        'destination_a'   => $this->getDestinationByNum($item->a_number, $apiUrl),
        'destination_b'   => $this->getDestinationByNum($item->b_number, $apiUrl),
        'destination_c'   => $this->getDestinationByNum($item->c_number, $apiUrl),
        'a_number'        => $item->a_number,
        'b_number'        => $item->b_number,
        'c_number'        => $item->c_number,
        'id'              => $item->id,
        'name'            => $item->name,
        'url'             => $requestUrl, // уже было
        'baseUrl'         => Yii::$app->params['isEuropean']
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
