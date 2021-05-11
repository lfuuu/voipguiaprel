<?php

namespace app\controllers\json;

use app\models\TestCall;
use Yii;
use app\classes\JsonController;
use app\classes\traits\TestResult;
use app\exceptions\FormValidationException;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class TestCallController extends JsonController
{
    use TestResult;
    
    const TEST_RESULT_DIVIDER_START = '2B2EKSTARTJSON';
    const TEST_RESULT_DIVIDER_STOP = '2B2EKSTOPJSON';
    
    const TEST_RESULT_DEFAULT_DEPTH = 1;
    const TEST_RESULT_INITIAL_DEPTH = 2;
    
    protected $createPermission = 'test_call_create';
    protected $listPermission = 'test_call_list';
    protected $editPermission = 'test_call_edit';
    protected $deletePermission = 'test_call_delete';
    protected $stepParamName = 'steps';
    
    private $_oldTestResultTypes = ['ERROR', 'RESULT', 'INFO'];

    public function actionList()
    {
        if (!\Yii::$app->user->can('test_call_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);

        return
            TestCall::find()
                ->select(['id', 'name', 'is_autotest'])
                ->where(['server_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead()
    {
        if (!\Yii::$app->user->can('test_call_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);
        $searchArray = $this->request['search_array'];
        $limit = $this->request['limit'];
        $offset = $this->request['offset'];
        
        $testGroupId = isset($searchArray['group_id']) ? $searchArray['group_id'] : '';
        $testResult = isset($searchArray['result']) ? $searchArray['result'] : false;
    
        if ($testGroupId == '') {
            $groupWhere = 'true';
        } else {
            $groupWhere = ['testgroup_id' => $testGroupId];
        }
        
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
    
        $query = TestCall::find()
                ->select(
                    [
                        'test_call.*',
                        new Expression('CASE WHEN tr.passed IS null OR now() AT TIME ZONE \'UTC\' - tr.tm::timestamp > INTERVAL \'1 HOUR\' THEN \'not_executed\' WHEN tr.passed = true THEN \'passed\' WHEN tr.passed = false THEN \'failed\' END as result'),
                        'tg.id as testgroup_id'
                    ])
                ->leftJoin('auth.test_result tr', 'tr.type = \'call\' and tr.id_call = auth.test_call.id')
                ->leftJoin('auth.test_group tg', 'tg.id = auth.test_call.testgroup_id')
                ->where($groupWhere)
                ->andWhere($resultWhere)
                ->orderBy('name')
                ->limit($limit)
                ->offset($offset)
                ->asArray();
    
        $countQuery = TestCall::find()
            ->select(['id'])
            ->leftJoin('auth.test_result tr', 'tr.type = \'call\' and tr.id_call = auth.test_call.id')
            ->where($groupWhere)
            ->andWhere($resultWhere);
    
        if (isset($searchArray['ignore_region']) && $searchArray['ignore_region'] === false) {
            $query->andWhere('test_call.server_id = :server_id');
            $query->addParams([':server_id' => $server->id]);
            $countQuery->andWhere('test_call.server_id = :server_id');
            $countQuery->addParams([':server_id' => $server->id]);
        }
    
        if (isset($searchArray['orig_trunk_name']) && $searchArray['orig_trunk_name']) {
            $query->andWhere('test_call.src_trunk_name = :trunk_name');
            $query->addParams([':trunk_name' => $searchArray['orig_trunk_name']]);
            $countQuery->andWhere('src_trunk_name = :trunk_name');
            $countQuery->addParams([':trunk_name' => $searchArray['orig_trunk_name']]);
        }
    
        if (isset($searchArray['term_trunk_name']) && $searchArray['term_trunk_name']) {
            $query->andWhere('test_call.dst_trunk_name = :trunk_name');
            $query->addParams([':trunk_name' => $searchArray['term_trunk_name']]);
            $countQuery->andWhere('dst_trunk_name = :trunk_name');
            $countQuery->addParams([':trunk_name' => $searchArray['term_trunk_name']]);
        }
    
        if (isset($searchArray['name']) && $searchArray['name']) {
            $query->andWhere('test_call.name ilike :name');
            $query->addParams([':name' => '%' . $searchArray['name'] . '%']);
            $countQuery->andWhere('name ilike :name');
            $countQuery->addParams([':name' => '%' . $searchArray['name'] . '%']);
        }
    
        if (isset($searchArray['id']) && $searchArray['id']) {
            $query->andWhere('test_call.id = :id');
            $query->addParams([':id' => $searchArray['id']]);
            $countQuery->andWhere('test_call.id = :id');
            $countQuery->addParams([':id' => $searchArray['id']]);
        }
    
        $data = $query->all();
        $count = $countQuery->count();
    
        return [
            'totalCount' => $count,
            'data' => $data
        ];
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('test_call_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = TestCall::find()
            ->select(['test_call.*', 'tr.tm', 'tr.received'])
            ->leftJoin('auth.test_result tr', 'tr.type = \'call\' and tr.id_call = auth.test_call.id')
            ->where(['test_call.id' => $this->request['id']])
            ->asArray()
            ->one();

        if ($item === null) {
            throw new HttpException(404, 'TestCall не найден');
        }

        return $item;
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('test_call_edit') && !\Yii::$app->user->can('test_call_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $result = [];
        
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('test_call_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getTestCallOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('test_call_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = TestCall::create($server);
            $result['log'] = ['data_before' => []];
        }

        $item->load($this->request, '');

        $transaction = TestCall::getDb()->beginTransaction();
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

    public function actionDelete()
    {
        if (!\Yii::$app->user->can('test_call_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = TestCall::findOne($this->request['id']);
        $item->delete();
    }

    public function actionResult()
    {
        if (!\Yii::$app->user->can('test_call_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = TestCall::findOne($this->request['id']); /** @var TestCall $item */

        if ($item === null) {
            throw new HttpException(404, 'TestCall не найден');
        }
    
        $apiUrl = $item->server->apiUrl;
        $apiParams = [
            'orig' => $item->orig ? 'true' : 'false',
            'connect_time' => $item->connect_time,
            'session_time' => $item->session_time,
            'src_route' => $item->src_trunk_name,
            'dst_route' => $item->dst_trunk_name,
            'src_number' => $item->src_number,
            'dst_number' => $item->dst_number,
            'redirect_number' => $item->redirect_number,
            'src_noa' => $item->src_noa,
            'dst_noa' => $item->dst_noa,
            'dst_replace' => $item->dst_replace
        ];

        if (isset($item->cpc)) {
            $apiParams['cpc'] =  $item->cpc;
        }
    
        if ($item->with_debug_info) {
            $apiParams['with_debug_info'] = 1;
        }

        if ($this->request['displayTreeView']) {
            $apiParams['trace_tree'] = 1;
        }
    
        if (isset($this->request['isReserve']) && $item->server->hostname_reserve) {
            $apiUrl = $item->server->apiUrlReserve;
            $apiParams['server_id'] = $item->server_id;
        }
    
        if (isset($this->request['isReserve2']) && $item->server->hostname_reserve_2) {
            $apiUrl = $item->server->apiUrlReserve2;
            $apiParams['server_id'] = $item->server_id;
        }
    
        if (isset($this->request['isDev']) && $item->server->hostname_dev) {
            $apiUrl = $item->server->apiUrlDev;
        }

        $request = $apiUrl . 'test/calc?' . http_build_query($apiParams);
        
        $apiParams['user'] = Yii::$app->user->getId();
        $apiParams['date'] = date('Y-m-d H:i:s');
    
        $requestForKey = $apiUrl . 'test/calc?' . http_build_query($apiParams);
    
        $key = md5($requestForKey);
    
        $response = file_get_contents($request);
    
        return [
            'item' => $item->toArray(),
            'key' => $key,
            'result' => $this->generateOldResult($response),
            'result_new' => $this->generateNewResult($response, $key),
            'url' => $request
        ];
    }

    private function generateOldResult($resultString)
    {
        $resultString = str_replace("\r", "", $resultString);

        if (strpos($resultString, self::TEST_RESULT_DIVIDER_START) !== false) {
            $resultArray = explode(self::TEST_RESULT_DIVIDER_START, $resultString);
            $resultArrayEnd = explode(self::TEST_RESULT_DIVIDER_STOP, $resultString);

            $resultArray = explode("\n", $resultArray[0]);
            $resultArray[] = trim($resultArrayEnd[1]);
        } else {
            $resultArray = explode("\n", $resultString);
        }

        $result = [];
        foreach ($resultArray as $text) {
            $m = explode('|', $text);
            $type = isset($m[0]) ? $m[0] : '';
            $action = isset($m[1]) ? $m[1] : '';
            $params = isset($m[2]) ? $m[2] : '';
    
            if (in_array($type, $this->_oldTestResultTypes)) {
                $result[] = [
                    'type' => $type,
                    'action' => $action,
                    'params' => $params,
                ];
            }
        }

        return $result;
    }
    
    private function generateNewResult($resultString, $key)
    {
        if (strpos($resultString, self::TEST_RESULT_DIVIDER_START) === false) {
            return null;
        }
    
        $resultString = str_replace("\r", "", $resultString);
        $resultString = str_replace("\n", "", $resultString);
    
        $resultArray = explode(self::TEST_RESULT_DIVIDER_START, $resultString);
    
        if (count($resultArray) > 1) {
            $resultArray = explode(self::TEST_RESULT_DIVIDER_STOP, $resultArray[1]);
        } else {
            $resultArray = explode(self::TEST_RESULT_DIVIDER_STOP, $resultArray[0]);
        }
    
        $tempResult = json_decode($resultArray[0], true);
    
        $result = $this->processResult($tempResult);
        
        Yii::$app->cache->set($key, $result);
        
        $finalResult = $this->findByPath($result, '', self::TEST_RESULT_INITIAL_DEPTH);
        
        return $finalResult;
    }
}
