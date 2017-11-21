<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\Server;
use app\models\TestAuth;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class TestAuthController extends JsonController
{
    const TEST_RESULT_DIVIDER_START = '2B2EKSTARTJSON';
    const TEST_RESULT_DIVIDER_STOP = '2B2EKSTOPJSON';

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

        return
            TestAuth::find()
                ->select(
                    [
                        'test_auth.*',
                        new Expression('CASE WHEN tr.passed IS null OR now() AT TIME ZONE \'UTC\' - tr.tm::timestamp > INTERVAL \'1 HOUR\' THEN \'not_executed\' WHEN tr.passed = true THEN \'passed\' WHEN tr.passed = false THEN \'failed\' END as result'),
                        'tg.id as testgroup_id'
                    ])
                ->leftJoin('auth.test_result tr', 'tr.type = \'auth\' and tr.id_auth = auth.test_auth.id')
                ->leftJoin('auth.test_group tg', 'tg.id = auth.test_auth.testgroup_id')
                ->where(['test_auth.server_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
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

        if ($this->request['displayTreeView']) {
            $apiParams['trace_tree'] = 1;
        }

        if (isset($this->request['isReserve']) && $item->server->hostname_reserve) {
            $apiUrl = $item->server->apiUrlReserve;
            $apiParams['server_id'] = $item->server_id;
        }

        $request = $apiUrl . 'test/auth?' . http_build_query($apiParams);

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
