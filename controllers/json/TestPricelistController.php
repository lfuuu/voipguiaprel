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
    const API_URL = 'http://reg10.mcntelecom.ru:8032/';
    
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
        $limit = $this->request['limit'];
        $offset = $this->request['offset'];
    
        $testGroupId = isset($searchArray['group_id']) ? $searchArray['group_id'] : '';
        $testResult = isset($searchArray['result']) ? $searchArray['result'] : false;
    
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
                break;
        }
        
        $query = TestPricelist::find()
            ->alias('tp')
            ->select(['tp.*', 'mcc.country as mcc_name', 'mnc.network as mnc_name', 'p.name as pricelist_name',
                new Expression('case 
                    when tp.location_id = 1 then \'Домашний регион\' 
                    when tp.location_id = 2 then \'Гостевой регион\' 
                    when tp.location_id = 3 then \'Международный регион\' 
                    end as location_name'),
                new Expression('CASE WHEN tr.passed IS null OR now() AT TIME ZONE \'UTC\' - tr.tm::timestamp > INTERVAL \'1 HOUR\' THEN \'not_executed\' WHEN tr.passed = true THEN \'passed\' WHEN tr.passed = false THEN \'failed\' END as result'),
                new Expression('\'#\' || s.id || \': \' || s.name as server_name')
            ])
            ->leftJoin('auth.test_result tr', 'tr.type = \'pricelist\' and tr.id_pricelist = tp.id')
            ->leftJoin('nnp.mcc as mcc', 'mcc.mcc = tp.mcc::text')
            ->leftJoin('nnp.mnc as mnc', 'mnc.mnc = tp.mnc::text and mnc.mcc = tp.mcc::text')
            ->leftJoin('billing_uu.pricelist as p', 'p.id = tp.pricelist_id')
            ->innerJoin('public.server as s', 's.id = tp.server_id')
            ->andWhere($resultWhere)
            ->orderBy('name')
            ->limit($limit)
            ->offset($offset)
            ->asArray();

        $countQuery = TestPricelist::find()
            ->alias('tp')
            ->select(['tp.id'])
            ->leftJoin('auth.test_result tr', 'tr.type = \'pricelist\' and tr.id_pricelist = tp.id')
            ->andWhere($resultWhere);
    
        if ($testGroupId != '') {
            $query->andWhere(['tp.test_pricelist_group_id' => $testGroupId]);
            $countQuery->andWhere(['tp.test_pricelist_group_id' => $testGroupId]);
        }
    
        if (isset($searchArray['name']) && $searchArray['name']) {
            $query->andWhere('tp.name ilike :name');
            $query->addParams([':name' => '%' . $searchArray['name'] . '%']);
            $countQuery->andWhere('tp.name ilike :name');
            $countQuery->addParams([':name' => '%' . $searchArray['name'] . '%']);
        }
    
        if (isset($searchArray['server_id']) && $searchArray['server_id']) {
            $query->andWhere('tp.server_id = :server_id');
            $query->addParams([':server_id' => $searchArray['server_id']]);
            $countQuery->andWhere('tp.server_id = :server_id');
            $countQuery->addParams([':server_id' => $searchArray['server_id']]);
        }
    
        if (isset($searchArray['pricelist_id']) && $searchArray['pricelist_id']) {
            $query->andWhere('tp.pricelist_id = :pricelist_id');
            $query->addParams([':pricelist_id' => $searchArray['pricelist_id']]);
            $countQuery->andWhere('tp.pricelist_id = :pricelist_id');
            $countQuery->addParams([':pricelist_id' => $searchArray['pricelist_id']]);
        }
    
        if (isset($searchArray['id']) && $searchArray['id']) {
            $query->andWhere('tp.id = :id');
            $query->addParams([':id' => $searchArray['id']]);
            $countQuery->andWhere('tp.id = :id');
            $countQuery->addParams([':id' => $searchArray['id']]);
        }
    
        $data = $query->all();
    
        $count = $countQuery->count();
    
        return [
            'totalCount' => $count,
            'data' => $data
        ];
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionGet()
    {
        if (!\Yii::$app->user->can('test_pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = TestPricelist::find()
            ->alias('tp')
            ->select(['tp.*', 'tr.tm', 'tr.received'])
            ->leftJoin('auth.test_result tr', 'tr.type = \'pricelist\' and tr.id_pricelist = tp.id')
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
        if (!\Yii::$app->user->can('test_pricelist_edit') && !\Yii::$app->user->can('test_pricelist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $result = [];
        
        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('test_pricelist_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getTestPricelistOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('test_pricelist_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = TestPricelist::create();
            $result['log'] = ['data_before' => []];
        }

        $item->load($this->request, '');

        $transaction = TestPricelist::getDb()->beginTransaction();
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
    public function actionResult()
    {
        if (!\Yii::$app->user->can('test_pricelist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $this->getTestPricelistOr404($this->request['id']);
        $server = Server::findOne($item['server_id']);
        
        $apiUrl = $server->apiUrl;
    
        $apiParams = [
            'cmd' => 'priceV2Calc',
            'num_a' => $item->a_number,
            'num_b' => $item->b_number,
            'mcc' => $item->mcc,
            'mnc' => $item->mnc,
            'location_id' => $item->location_id,
            'pricelist_id' => $item->pricelist_id,
            'orig' => $item->is_orig ? 'true' : 'false',
            'test_mode' => 'true',
            'date' => $item->mock_current_date
        ];
    
        if ($item->with_debug_info) {
            $apiParams['with_debug_info'] = 1;
        }
    
        if ($item->sim_partner_id) {
            $apiParams['sim_partner_id'] = $item->sim_partner_id;
        }
    
        if ($item->sim_profile_id) {
            $apiParams['sim_profile_id'] = $item->sim_profile_id;
        }
        
        $request = $apiUrl . 'test/nnpcalc?' . http_build_query($apiParams);
        
        $response = file_get_contents($request);
        
        return [
            'steps' => [json_decode($response, true)],
            'number_range_a' => $this->getNumberRangeByNum($item->a_number, $apiUrl),
            'number_range_b' => $this->getNumberRangeByNum($item->b_number, $apiUrl),
            'destination_a' => $this->getDestinationByNum($item->a_number, $apiUrl),
            'destination_b' => $this->getDestinationByNum($item->b_number, $apiUrl),
            'a_number' => $item->a_number,
            'b_number' => $item->b_number,
            'id' => $item->id,
            'name' => $item->name,
            'url' => $request
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
        
        $apiUrl = self::API_URL;
        
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
        $apiParams = [
            'cmd' => $cmd,
            'num' => $number
        ];
        
        $request = $apiUrl . 'test/nnpcalc?' . http_build_query($apiParams);
        
        $response = file_get_contents($request);
        $result = json_decode($response, true);
        
        if (!empty($fields) && !empty($result)) {
            foreach ($result as $key => $value) {
                if (array_key_exists($key, $fields)) {
                    $newItem = $fields[$key]::findOne(['id' => $value]);
                    if ($newItem) {
                        $result[$key.'_name'] = $newItem->name;
                    }
                }
            }
        }
        
        if (isset($result) && is_array($result)) {
            ksort($result);
        }
        
        return $result;
    }
}
