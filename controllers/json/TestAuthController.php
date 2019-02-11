<?php

namespace app\controllers\json;

use app\classes\JsonController;
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
    const TEST_RESULT_DIVIDER_START = '2B2EKSTARTJSON';
    const TEST_RESULT_DIVIDER_STOP = '2B2EKSTOPJSON';
    
    const TEST_RESULT_DEFAULT_DEPTH = 1;
    const TEST_RESULT_INITIAL_DEPTH = 2;
    
    const TEST_DIRECTION_MAIN = 1;
    const TEST_DIRECTION_RESERVE = 2;
    const TEST_DIRECTION_RESERVE_2 = 3;
    const TEST_DIRECTION_DEV = 4;
    
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
        $testGroupId = $this->request['test_group_id'];
        $testResult = $this->request['test_result'];
        $limit = $this->request['limit'];
        $offset = $this->request['offset'];
        
        if ($testGroupId == 'undefined') {
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

        $data = TestAuth::find()
                ->select(
                    [
                        'test_auth.*',
                        new Expression('CASE WHEN tr.passed IS null OR now() AT TIME ZONE \'UTC\' - tr.tm::timestamp > INTERVAL \'1 HOUR\' THEN \'not_executed\' WHEN tr.passed = true THEN \'passed\' WHEN tr.passed = false THEN \'failed\' END as result'),
                        'tg.id as testgroup_id'
                    ])
                ->leftJoin('auth.test_result tr', 'tr.type = \'auth\' and tr.id_auth = auth.test_auth.id')
                ->leftJoin('auth.test_group tg', 'tg.id = auth.test_auth.testgroup_id')
                ->where(['test_auth.server_id' => $server->id])
                ->andWhere($groupWhere)
                ->andWhere($resultWhere)
                ->orderBy('name')
                ->limit($limit)
                ->offset($offset)
                ->asArray()
                ->all();
    
        $count = TestAuth::find()
            ->select(['id'])
            ->leftJoin('auth.test_result tr', 'tr.type = \'auth\' and tr.id_auth = auth.test_auth.id')
            ->where(['auth.test_auth.server_id' => $server->id])
            ->andWhere($groupWhere)
            ->andWhere($resultWhere)
            ->count();
        
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
            ->leftJoin('auth.test_result tr', 'tr.type = \'auth\' and tr.id_auth = auth.test_auth.id')
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
        
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('test_auth_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getTestAuthOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('test_auth_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = TestAuth::create($server);
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
            'headers' => $item->headers
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
            $apiParams['server_id'] = $item->server_id;
        }
    
        if (isset($this->request['isReserve2']) && $item->server->hostname_reserve_2) {
            $direction = self::TEST_DIRECTION_RESERVE_2;
            $apiUrl = $item->server->apiUrlReserve2;
            $apiParams['server_id'] = $item->server_id;
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
        
        foreach ($resultArray as $text) {
            $m = explode('|', $text);
            $type = isset($m[0]) ? $m[0] : '';
            $action = isset($m[1]) ? $m[1] : '';
            $params = isset($m[2]) ? $m[2] : '';
            
            if (in_array($type, $this->_oldTestResultTypes)) {
                if ($type == 'RESULT' && $ttl > 1) {
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
                            $direction, $traceTrunk->trunk_name, $traceTrunk->server_id, $ttl, $redirectNumber, $srcNumber);
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
    
    private function trace($trunkName, $traceToRegions, $apiParams, $direction, $origTrunk, $origServerId, $ttl, $redirectNumber = null, $srcNumber = null)
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
                'ttl' => $ttl
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
    
    private function processResult($result)
    {
        $newResult = [];
    
        foreach ($result[0]['steps'] as $stepKey => $step) {
            $newResult['steps'][$stepKey] = $this->processItemRecursive($step, $stepKey);
        }
    
        return $newResult;
    }
    
    private function processItemRecursive($item, $path = '')
    {
        $newItem = $item;
    
        $newItem['path'] = $path;
    
        if (array_key_exists('steps', $item) && count($item['steps']) > 0) {
            foreach ($item['steps'] as $stepKey => $step) {
                $newItem['steps'][$stepKey] = $this->processItemRecursive($step, $path . ',' . $stepKey);
            }
        }
        
        return $newItem;
    }
    
    private function findByPath($result, $path, $depth = self::TEST_RESULT_DEFAULT_DEPTH)
    {
        $newResult = [];
        
        if (empty($path)) {
            if (!empty($result) && array_key_exists('steps', $result)) {
                foreach ($result['steps'] as $stepKey => $step) {
                    $newResult['steps'][$stepKey] = $this->findByPathRecursive($step, $depth - 1);
                }
            }
        } else {
            $pathArray = explode(',', $path);
            
            $newResult = &$result;
    
            foreach ($pathArray as $key) {
                $newResult = &$newResult['steps'][$key];
            }
            
            if (array_key_exists('steps', $newResult)) {
                foreach ($newResult['steps'] as &$step) {
                    if (array_key_exists('steps', $step)) {
                        $step['steps'] = [];
                    }
                }
            }
        }
        
        return $newResult;
    }
    
    private function findByPathRecursive($item, $depth)
    {
        $newItem = $item;
    
        if ($depth == 0) {
            if (array_key_exists('steps', $item)) {
                $newItem['steps'] = [];
            }
        } else {
            if (array_key_exists('steps', $item) && count($item['steps']) > 0) {
                foreach ($item['steps'] as $stepKey => $step) {
                    $newItem['steps'][$stepKey] = $this->findByPathRecursive($step, $depth - 1);
                }
            }
        }
    
        return $newItem;
    }
    
    public function actionDescend()
    {
        if (!\Yii::$app->user->can('test_auth_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $path = $this->request['path'];
        $key = $this->request['key'];
        
        $data = Yii::$app->cache->get($key);
        
        if ($data === false) {
            throw new Exception('No data in cache');
        }
        
        return $this->findByPath($data, $path);
    }
    
    public function actionClearCache()
    {
        if (!\Yii::$app->user->can('test_auth_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        Yii::$app->cache->flush();
        
        return ['success' => 1];
    }
}
