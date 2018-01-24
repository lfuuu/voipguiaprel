<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\Server;
use app\models\TestAuth;
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
    
    const TEST_RESULT_OLD_TYPES = ['ERROR', 'RESULT', 'INFO'];

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
                ->andWhere(['auth.test_auth.testgroup_id' => $testGroupId])
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
            ->andWhere(['testgroup_id' => $testGroupId])
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

        $apiUrl = $item->server->apiUrl;
        $apiParams = [
            'trunk_name' => $item->trunk_name,
            'src_number' => $item->src_number,
            'dst_number' => $item->dst_number,
            'redirect_number' => $item->redirect_number,
            'src_noa' => $item->src_noa,
            'dst_noa' => $item->dst_noa
        ];
        
        if ($item->cpc) {
            $apiParams['cpc'] = $item->cpc;
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
        
        $request = $apiUrl . 'test/auth?' . http_build_query($apiParams);
        
        $apiParams['user'] = Yii::$app->user->getId();
        
        $requestForKey = $apiUrl . 'test/auth?' . http_build_query($apiParams);
        
        $key = md5($requestForKey);
        
        $response = file_get_contents($request);
    
        return [
            'item' => $item->toArray(),
            'key' => $key,
            'result' => $this->generateOldResult($response),
            'result_new' => $this->generateNewResult($response, $key)
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
            
            if (in_array($type, self::TEST_RESULT_OLD_TYPES)) {
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
        $result = Yii::$app->cache->getOrSet($key, function () use ($resultString) {
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
            
            $processedResult = $this->processResult($tempResult);
    
            return $processedResult;
        });
        
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
            foreach ($result['steps'] as $stepKey => $step) {
                $newResult['steps'][$stepKey] = $this->findByPathRecursive($step, $depth - 1);
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
}
