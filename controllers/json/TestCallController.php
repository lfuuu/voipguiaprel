<?php

namespace app\controllers\json;

use app\models\TestCall;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class TestCallController extends JsonController
{
    const TEST_RESULT_DIVIDER_START = '2B2EKSTARTJSON';
    const TEST_RESULT_DIVIDER_STOP = '2B2EKSTOPJSON';

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
    
        $data = TestCall::find()
                ->select(
                    [
                        'test_call.*',
                        new Expression('CASE WHEN tr.passed IS null OR now() AT TIME ZONE \'UTC\' - tr.tm::timestamp > INTERVAL \'1 HOUR\' THEN \'not_executed\' WHEN tr.passed = true THEN \'passed\' WHEN tr.passed = false THEN \'failed\' END as result'),
                        'tg.id as testgroup_id'
                    ])
                ->leftJoin('auth.test_result tr', 'tr.type = \'call\' and tr.id_call = auth.test_call.id')
                ->leftJoin('auth.test_group tg', 'tg.id = auth.test_call.testgroup_id')
                ->where(['test_call.server_id' => $server->id])
                ->andWhere(['auth.test_call.testgroup_id' => $testGroupId])
                ->andWhere($resultWhere)
                ->orderBy('name')
                ->limit($limit)
                ->offset($offset)
                ->asArray()
                ->all();
    
        $count = TestCall::find()
            ->select(['id'])
            ->leftJoin('auth.test_result tr', 'tr.type = \'call\' and tr.id_call = auth.test_call.id')
            ->where(['auth.test_call.server_id' => $server->id])
            ->andWhere(['testgroup_id' => $testGroupId])
            ->andWhere($resultWhere)
            ->count();
    
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
        
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('test_call_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = $this->getTestCallOr404($this->request['id']);
        } else {
            if (!\Yii::$app->user->can('test_call_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = TestCall::create($server);
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
            'src_route' => $item->trunk_name,
            'dst_route' => $item->trunk_name,
            'src_number' => $item->src_number,
            'dst_number' => $item->dst_number,
            'redirect_number' => $item->redirect_number,
            'src_noa' => $item->src_noa,
            'dst_noa' => $item->dst_noa
        ];

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

        $response = file_get_contents($request);

        return [
            'item' => $item->toArray(),
            'result' => $this->generateOldResult($response),
            'result_new' => $this->generateNewResult($response)
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

            $result[] = [
                'type' => $type,
                'action' => $action,
                'params' => $params,
            ];
        }

        return $result;
    }

    private function generateNewResult($resultString)
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

        return json_decode($resultArray[0], true);
    }
}
