<?php

namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\classes\traits\TestResult;
use app\models\auth\SmsTrunk;
use app\models\ServerOcs;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class TestAuthController extends JsonController
{
    use TestResult;
    const TEST_RESULT_DEFAULT_DEPTH = 1;
    const TEST_RESULT_INITIAL_DEPTH = 2;

    const TEST_RESULT_DIVIDER_START = '2B2EKSTARTJSON';
    const TEST_RESULT_DIVIDER_STOP = '2B2EKSTOPJSON';
    protected $modelName = 'app\models\auth\SmsTestAuth';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $readWhere = ['server_id'];
    protected $createPermission = 'sms_test_auth_create';
    protected $listPermission = 'sms_test_auth_list';
    protected $editPermission = 'sms_test_auth_edit';
    protected $deletePermission = 'sms_test_auth_delete';
    private $stepParamName = 'nodes';

    private $_oldTestResultTypes = ['ERROR', 'RESULT', 'INFO', 'HEADER'];

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws HttpException
     */
    public function actionRead()
    {
        if (!\Yii::$app->user->can('sms_test_auth_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        $modelName = $this->modelName;
        $searchArray = $this->request['search_array'];
        $testGroupId = isset($searchArray['group_id']) ? $searchArray['group_id'] : '';
        if ($testGroupId == '') {
            $groupWhere = 'true';
        } else {
            $groupWhere = ['auth.a2p_test_auth.a2p_testgroup_id' => $testGroupId];
        }
        
        $query = $modelName::find()
            ->select([
                'auth.a2p_test_auth.*',
                'testgroup_name'   => 'tg.group_name',
                'trunk_name_out'   => 'r.name',
                'passed'           => 'trs.passed'
            ])
            ->leftJoin('auth.a2p_testgroup tg', 'tg.id = auth.a2p_test_auth.a2p_testgroup_id')
            ->leftJoin('auth.a2psms_route r', 'cast(r.id as varchar(10)) = auth.a2p_test_auth.trunk_name')
            ->leftJoin('auth.a2p_test_result trs', 'trs.id_auth = auth.a2p_test_auth.id')
            ->where($groupWhere)
            ->orderBy('name')
            ->asArray();

        if (isset($searchArray['name']) && $searchArray['name']) {
            $query->andWhere('name ilike :name');
            $query->addParams([':name' => '%' . $searchArray['name'] . '%']);
        }
        if (isset($searchArray['id']) && $searchArray['id']) {
            $query->andWhere('auth.a2p_test_auth.id = :id');
            $query->addParams([':id' => $searchArray['id']]);
        }
        if (isset($searchArray['result']) && $searchArray['result'] !== '') {
            if ($searchArray['result'] == 'success') {
                $query->andWhere(['trs.passed' => true]);
            } elseif ($searchArray['result'] == 'failure') {
                $query->andWhere(['trs.passed' => false]);
            }
        }
        
        $data = $query->all();

        return $data;
    }

    public function actionResult()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;
        $item = $modelName::findOne($this->request['id']);

        if ($item === null) {
            throw new HttpException(404, $this->modelName . ' не найден');
        }
        $server = ServerOcs::findOne($item->server_id);

        if ($server === null) {
            throw new HttpException(404, 'Сервер для ' . $this->modelName . ' не найден');
        }
        $isReserve = (isset($this->request['is_reserve']) && $this->request['is_reserve']) ? true : false;
        $apiUrl = $isReserve ? $server->camel_reserve : $server->camel_gw;

        $trunk = SmsTrunk::findOne(['id' => $item->trunk_name]);
        if ($trunk === null) {
            throw new HttpException(404, 'Транк не найден');
        }
        $request = $apiUrl . 'api/get.dst_route?' 
            . 'a_num=' . $item->src_number 
            . '&b_num=' . $item->dst_number 
            . '&src_route=' . $trunk->name;
        $request = str_replace(' ', '+', $request);

        $context = stream_context_create([
            'http' => ['ignore_errors' => true],
        ]);
        $response = file_get_contents($request, false, $context);

        $apiParams['user'] = \Yii::$app->user->getId();
        $apiParams['date'] = date('Y-m-d H:i:s');

        $requestForKey = $apiUrl . 'test/auth?' . http_build_query($apiParams);
        $key = md5($requestForKey);

        return [
            'item'   => $item->toArray(),
            'name'   => 'root',
            'key'    => $key,
            'result' => $this->generateNewResult($response, $key),
            'url'    => $request
        ];
    }

    private function generateNewResult($resultString, $key)
    {
        $resultString = str_replace(["\r", "\n", "\t"], "", $resultString);
        $resultArray = explode(self::TEST_RESULT_DIVIDER_START, $resultString);

        $tempResult = json_decode($resultArray[0], true);
        $tempResult['trace'] = json_decode($tempResult['trace'], true);

        $result = $this->processResult($tempResult['trace'], true);
        \Yii::$app->cache->set($key, $result);
        $finalResult = $this->findByPath($result, '', 4);

        return $finalResult;
    }
}
