<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\classes\traits\TestResult as TestResult;
use app\exceptions\FormValidationException;
use app\models\Server;
use app\models\TestAuth;
use app\models\Trunk;
use yii\base\Exception;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use Yii;

class TestAuthController extends JsonController
{
    use TestResult;
    
    const TEST_RESULT_DIVIDER_START = '2B2EKSTARTJSON';
    const TEST_RESULT_DIVIDER_STOP = '2B2EKSTOPJSON';
    
    const TEST_RESULT_DEFAULT_DEPTH = 1;
    const TEST_RESULT_INITIAL_DEPTH = 2;
    
    const TEST_DIRECTION_MAIN = 1;
    const TEST_DIRECTION_RESERVE = 2;
    const TEST_DIRECTION_RESERVE_2 = 3;
    const TEST_DIRECTION_DEV = 4;
    const TEST_DIRECTION_1001 = 5;
    const TEST_DIRECTION_1002 = 6;
    
    protected $createPermission = 'test_auth_create';
    protected $listPermission = 'test_auth_list';
    protected $editPermission = 'test_auth_edit';
    protected $deletePermission = 'test_auth_delete';
    protected $stepParamName = 'steps';
    
    private $_oldTestResultTypes = ['ERROR', 'RESULT', 'INFO', 'HEADER'];

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws HttpException
     */
    public function actionList()
    {
        if (!\Yii::$app->user->can('test_auth_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);

        return
            TestAuth::find()
                ->select(['id', 'name', 'is_autotest'])
                ->where(['server_id' => $server->id])
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
        if (!\Yii::$app->user->can('test_auth_list')) {
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
            $groupWhere = ['auth.test_auth.testgroup_id' => $testGroupId];
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

        $lastTrSql = "
        (
          SELECT DISTINCT ON (id_auth)
                 id_auth, passed, tm
          FROM auth.test_result
          WHERE type = 'auth'" .
          ($server->id == 11 ? " AND server_id = 11 AND instance_id = 1001" : "") . "
          ORDER BY id_auth, tm DESC
        ) tr";

        $query = TestAuth::find()
            ->select(
                [
                    'test_auth.*',
                    new Expression('CASE WHEN tr.passed IS null OR now() AT TIME ZONE \'UTC\' - tr.tm::timestamp > INTERVAL \'1 HOUR\' THEN \'not_executed\' WHEN tr.passed = true THEN \'passed\' WHEN tr.passed = false THEN \'failed\' END as result'),
                    'tg.id as testgroup_id'
                ])
            ->leftJoin($lastTrSql, 'tr.id_auth = auth.test_auth.id') // ← было: 'auth.test_result tr' ...
            ->leftJoin('auth.test_group tg', 'tg.id = auth.test_auth.testgroup_id')
            ->where($groupWhere)
            ->andWhere($resultWhere)
            ->orderBy('name')
            ->limit($limit)
            ->offset($offset)
            ->asArray();

        $countQuery = TestAuth::find()
            ->select(['id'])
            ->leftJoin($lastTrSql, 'tr.id_auth = auth.test_auth.id') // ← симметрично
            ->where($groupWhere)
            ->andWhere($resultWhere);

        if (isset($searchArray['ignore_region']) && $searchArray['ignore_region'] === false) {
            $query->andWhere('test_auth.server_id = :server_id');
            $query->addParams([':server_id' => $server->id]);
            $countQuery->andWhere('test_auth.server_id = :server_id');
            $countQuery->addParams([':server_id' => $server->id]);
        }
        if (isset($searchArray['trunk_name']) && $searchArray['trunk_name']) {
            $query->andWhere('test_auth.trunk_name = :trunk_name');
            $query->addParams([':trunk_name' => $searchArray['trunk_name']]);
            $countQuery->andWhere('trunk_name = :trunk_name');
            $countQuery->addParams([':trunk_name' => $searchArray['trunk_name']]);
        }
        if (isset($searchArray['name']) && $searchArray['name']) {
            $query->andWhere('test_auth.name ilike :name');
            $query->addParams([':name' => '%' . $searchArray['name'] . '%']);
            $countQuery->andWhere('name ilike :name');
            $countQuery->addParams([':name' => '%' . $searchArray['name'] . '%']);
        }
        if (isset($searchArray['id']) && $searchArray['id']) {
            $query->andWhere('test_auth.id = :id');
            $query->addParams([':id' => $searchArray['id']]);
            $countQuery->andWhere('test_auth.id = :id');
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
        if (!\Yii::$app->user->can('test_auth_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $item = TestAuth::find()
            ->select(['test_auth.*', 'tr.tm', 'tr.received'])
            ->leftJoin(
                'auth.test_result tr',
                "tr.type = 'auth' 
                 AND tr.id_auth = auth.test_auth.id 
                 AND tr.server_id = tr.instance_id"
            )
            ->where(['test_auth.id' => $this->request['id']])
            ->asArray()
            ->one();
    
        if ($item === null) {
            throw new HttpException(404, 'TestAuth не найден');
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
        if (!\Yii::$app->user->can('test_auth_edit') && !\Yii::$app->user->can('test_auth_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $result = [];
        
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('test_auth_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getTestAuthOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($item)];
        } else {
            if (!\Yii::$app->user->can('test_auth_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = TestAuth::create($server);
            $result['log'] = ['data_before' => []];
        }

        $item->load($this->request, '');

        $transaction = TestAuth::getDb()->beginTransaction();
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
        if (!\Yii::$app->user->can('test_auth_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = TestAuth::findOne($this->request['id']);
        $item->delete();
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionResult()
    {
        if (!\Yii::$app->user->can('test_auth_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $item = TestAuth::findOne($this->request['id']); /** @var TestAuth $item */
        if ($item === null) {
            throw new HttpException(404, 'TestAuth не найден');
        }

        $direction = self::TEST_DIRECTION_MAIN;
        $apiUrl = $item->server->apiUrl;
        $apiParams = [
            'trunk_name' => $item->trunk_name,
            'src_number' => $item->src_number,
            'dst_number' => $item->dst_number,
            'redirect_number' => $item->redirect_number,
            'src_noa' => $item->src_noa,
            'dst_noa' => $item->dst_noa,
            'router_version' => $item->router_version,
            'headers' => $item->headers,
            'server_id' => $item->server_id
        ];

        if ($item->with_debug_info) {
            $apiParams['with_debug_info'] = 1;
        }

        if ($item->cpc) {
            $apiParams['cpc'] = $item->cpc;
        }

        if ($this->request['displayTreeView']) {
            $apiParams['trace_tree'] = 1;
        }

        if (isset($this->request['isReserve']) && $item->server->hostname_reserve) {
            $direction = self::TEST_DIRECTION_RESERVE;
            $apiUrl = $item->server->apiUrlReserve;
        }

        if (isset($this->request['isReserve2']) && $item->server->hostname_reserve_2) {
            $direction = self::TEST_DIRECTION_RESERVE_2;
            $apiUrl = $item->server->apiUrlReserve2;
        }

        if (isset($this->request['is1001']) && $item->server->hostname_1001) {
            $direction = self::TEST_DIRECTION_1001;
            $apiUrl = $item->server->apiUrl1001;
        }

        if (isset($this->request['is1002']) && $item->server->hostname_1002) {
            $direction = self::TEST_DIRECTION_1002;
            $apiUrl = $item->server->apiUrl1002;
        }

        if (isset($this->request['isDev']) && $item->server->hostname_dev) {
            $direction = self::TEST_DIRECTION_DEV;
            $apiUrl = $item->server->apiUrlDev;
        }

        if (isset($this->request['ttl']) && $this->request['ttl'] == 'none') {
            $ttl = 0;
        } else {
            $ttl = $item->ttl;
            if (empty($ttl)) {
                $ttl = 0;
            }
        }

        $request = $apiUrl . 'test/auth?' . http_build_query($apiParams);
        $apiParams['user'] = Yii::$app->user->getId();
        $apiParams['date'] = date('Y-m-d H:i:s');
        $requestForKey = $apiUrl . 'test/auth?' . http_build_query($apiParams);
        $key = md5($requestForKey);

        $response = file_get_contents($request);
        list($result, $trace) = $this->generateOldResult($response, $item->server, $apiParams, $direction, $ttl);

        return [
            'item' => $item->toArray(),
            'name' => 'root',
            'key' => $key,
            'result' => $result,
            'result_new' => $this->generateNewResult($response, $key),
            'trace' => $trace,
            'ttl' => $ttl,
            'url' => $request
        ];
    }

    public function actionTrace()
    {
        if (!\Yii::$app->user->can('test_auth_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $direction = self::TEST_DIRECTION_MAIN;

        $apiParams = [
            'src_number' => $this->request['src_number'],
            'dst_number' => $this->request['dst_number'],
            'redirect_number' => $this->request['redirect_number'],
            'src_noa' => $this->request['src_noa'],
            'dst_noa' => $this->request['dst_noa'],
            'router_version' => $this->request['router_version'],
            'trace_tree' => 1,
            'headers' => $this->request['headers']
        ];
    
        if ($this->request['with_debug_info']) {
            $apiParams['with_debug_info'] = 1;
        }
        
        if (isset($this->request['isReserve'])) {
            $direction = self::TEST_DIRECTION_RESERVE;
        }
        
        if (isset($this->request['isReserve2'])) {
            $direction = self::TEST_DIRECTION_RESERVE_2;
        }
        
        if (isset($this->request['isDev'])) {
            $direction = self::TEST_DIRECTION_DEV;
        }
    
        return $this->trace($this->request['trunk_name'], $this->request['trace_to_regions'], $apiParams,
            $direction, $this->request['orig_trunk'], $this->request['server_id'], $this->request['ttl']);
    }
    
    private function generateOldResult($resultString, $server, $apiParams, $direction, $ttl = 0)
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
        
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0;
        
        $result = [];
        $trace = [];
        $headers = [];
        
        foreach ($resultArray as $text) {
            $m = explode('|', $text);
            $type = isset($m[0]) ? $m[0] : '';
            $action = isset($m[1]) ? $m[1] : '';
            $params = isset($m[2]) ? $m[2] : '';
            
            if (in_array($type, $this->_oldTestResultTypes)) {
                if ($type == 'HEADER') {
                    $actionArray = explode(': ', $action, 2);
                    $headers[$actionArray[0]] = $actionArray[1];
    
                    $result[] = [
                        'type' => $type,
                        'action' => $action,
                        'params' => $params,
                        'is_not_empty_params' => false,
                        'is_params_array' => false
                    ];
                } elseif ($type == 'RESULT' && $ttl > 1) {
                    $headersJson = json_encode($headers);
                    
                    $paramsArray = explode(',', $params);
                    
                    --$ttl;
                    
                    $redirectNumber = null;
                    $srcNumber = null;
                    
                    foreach ($paramsArray as $item) {
                        //Если в ответе есть редирект, то используем его для дальнейших тестов.
                        if (strpos($item, 'RN') !== false) {
                            $tmp = explode(' ', $item);
                            $redirectNumber = $tmp[3];
                            continue;
                        }

                        //Если в ответе есть calling, то используем его как номер А для дальнейших тестов.
                        if (strpos($item, 'calling') !== false) {
                            $tmp = explode(' ', $item);
                            $srcNumber = $tmp[3];
                            continue;
                        }
                    }

                    $displayParams = [];
                    $isNotEmptyParams = false;
                    $isParamsArray = false;
    
                    foreach ($paramsArray as $trunkName) {
                        $isParamsArray = true;
                        
                        if (!empty($trunkName)) {
                            $isNotEmptyParams = true;
                        }
                        
                        $trunk = Trunk::find()
                            ->where('auth.trunk.trunk_name = \'' . $trunkName . '\'')
                            ->andWhere("(auth.trunk.server_id in (select id from public.server where hub_id = ".$hub_id.") and sw_shared) or auth.trunk.server_id = ".$server->id)
                            ->andWhere('our_trunk = true')
                            ->andWhere('back_trunk is not null')
                            ->one();
                        
                        if (empty($traceTrunk) && !empty($trunk)) {
                            $traceTrunk = $trunk;
                        }
                        
                        if (!empty($trunk)) {
                            $item = [
                                'name' => $trunkName,
                                'is_url' => true,
                                'back_trunk' => $trunk->back_trunk,
                                'trace_to_regions' => $trunk->trace_to_regions
                            ];
                        } else {
                            $item = [
                                'name' => $trunkName,
                                'is_url' => false,
                                'back_trunk' => '',
                                'trace_to_regions' => ''
                            ];
                        }
                        
                        $displayParams[] = $item;
                    }
                    
                    $result[] = [
                        'type' => $type,
                        'action' => $action,
                        'params' => $displayParams,
                        'is_not_empty_params' => $isNotEmptyParams,
                        'is_params_array' => $isParamsArray
                    ];
                    
                    if (!empty($traceTrunk)) {
                        $trace[$params] = $this->trace($traceTrunk->back_trunk, $traceTrunk->trace_to_regions, $apiParams,
                            $direction, $traceTrunk->trunk_name, $traceTrunk->server_id, $ttl, $redirectNumber, $srcNumber,
                            $headersJson);
                    }
                } else {
                    $result[] = [
                        'type' => $type,
                        'action' => $action,
                        'params' => $params,
                        'is_not_empty_params' => false,
                        'is_params_array' => false
                    ];
                }
            }
        }
        
        return array($result, $trace);
    }
    
    private function trace($trunkName, $traceToRegions, $apiParams, $direction, $origTrunk, $origServerId, $ttl, $redirectNumber = null, $srcNumber = null, $headers = '')
    {
        if (empty($traceToRegions)) {
            return [];
        }
        
        $del = array(' ', ',', ';', '.', "\n");
    
        $serverIds = explode($del[0], str_replace($del, $del[0], $traceToRegions));
        
        if (count($serverIds) < 1) {
            return [];
        }
        
        $serverForSearch = Server::find()->where('id = ' . $serverIds[0])->one();
    
        $hub_id = $serverForSearch->hub_id > 0 ? $serverForSearch->hub_id : 0;
        
        $trunk = Trunk::find()
            ->where('auth.trunk.trunk_name = \'' . $trunkName . '\'')
            ->andWhere("(auth.trunk.server_id in (select id from public.server where hub_id = ".$hub_id.") and sw_shared) or auth.trunk.server_id = ".$serverForSearch->id)
            ->one();
    
        if (!empty($trunk)) {
            $server = Server::find()->where('id = ' . $trunk->server_id)->one();
            
            switch ($direction) {
                case self::TEST_DIRECTION_MAIN:
                    $apiUrl = $server->apiUrl;
                    break;
                case self::TEST_DIRECTION_RESERVE:
                    $apiUrl = $server->apiUrlReserve;
                    break;
                case self::TEST_DIRECTION_RESERVE_2:
                    $apiUrl = $server->apiUrlReserve2;
                    break;
                case self::TEST_DIRECTION_DEV:
                    $apiUrl = $server->apiUrlDev;
                    break;
                default:
                    $apiUrl = $server->apiUrl;
                    break;
            }
        
            $apiParams['trunk_name'] = $trunkName;
            $apiParams['server_id'] = $server->id;
            
            if ($redirectNumber) {
                $apiParams['redirect_number'] = $redirectNumber;
            }
            
            if ($srcNumber) {
                $apiParams['src_number'] = $srcNumber;
            }
    
            if ($headers) {
                $apiParams['headers'] = $headers;
            }
            
            $request = $apiUrl . 'test/auth?' . http_build_query($apiParams);
    
            $apiParams['user'] = Yii::$app->user->getId();
    
            $requestForKey = $apiUrl . 'test/auth?' . http_build_query($apiParams);
    
            $key = md5($requestForKey);
    
            $response = file_get_contents($request);
    
            list($result, $trace) = $this->generateOldResult($response, $server, $apiParams, $direction, $ttl);
    
            $origServer = Server::find()->where('id = ' . $origServerId)->one();
            
            return [
                'name' => $trunkName,
                'trunk_id' => $trunk->id,
                'server_id' => $server->id,
                'server_name' => $server->name,
                'orig_name' => $origTrunk,
                'orig_server_id' => $origServerId,
                'orig_server_name' => $origServer->name,
                'key' => $key,
                'result' => $result,
                'trace' => $trace,
                'ttl' => $ttl,
                'url' => $request,
                'headers' => $headers
            ];
        } else {
            return [];
        }
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
