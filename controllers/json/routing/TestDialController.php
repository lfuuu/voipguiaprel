<?php

namespace app\controllers\json\routing;

use app\classes\JsonController;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class TestDialController extends JsonController
{
    protected $modelName = 'app\models\auth\TestDial';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $createPermission = 'test_dial_create';
    protected $listPermission =   'test_dial_list';
    protected $editPermission =   'test_dial_edit';
    protected $deletePermission = 'test_dial_delete';


    public function actionRead()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $server = $this->getServerOr404($this->request['server_id']);
        $searchArray = $this->request['search_array'];
        $limit = $this->request['limit'];
        $offset = $this->request['offset'];

        $testGroupId = isset($searchArray['group_id']) ? $searchArray['group_id'] : '';
        
        if ($testGroupId == '') {
            $groupWhere = 'true';
        } else {
            $groupWhere = ['auth.test_dial.testgroup_id' => $testGroupId];
        }

        $modelName = $this->modelName;

        $query =
            $modelName::find()
                ->select(['auth.test_dial.*', 't.name as term_trunk_name'])
                ->orderBy($this->nameParamName)
                ->innerJoin('auth.trunk t', 't.id = auth.test_dial.term_trunk_id')
                ->where($groupWhere)
                ->limit($limit)
                ->offset($offset)
                ->asArray();

        $countQuery = $modelName::find()
            ->select(['id'])
            ->where($groupWhere);
            
        if (isset($searchArray['ignore_region']) && $searchArray['ignore_region'] === false) {
            $query->andWhere('auth.test_dial.server_id = :server_id');
            $query->addParams([':server_id' => $server->id]);
            $countQuery->andWhere('test_dial.server_id = :server_id');
            $countQuery->addParams([':server_id' => $server->id]);
        }
    
        if (isset($searchArray['term_trunk_id']) && $searchArray['term_trunk_id']) {
            $query->andWhere('auth.test_dial.term_trunk_id = :term_trunk_id');
            $query->addParams([':term_trunk_id' => $searchArray['term_trunk_id']]);
            $countQuery->andWhere('term_trunk_id = :term_trunk_id');
            $countQuery->addParams([':term_trunk_id' => $searchArray['term_trunk_id']]);
        }
    
        if (isset($searchArray['name']) && $searchArray['name']) {
            $query->andWhere('auth.test_dial.name ilike :name');
            $query->addParams([':name' => '%' . $searchArray['name'] . '%']);
            $countQuery->andWhere('name ilike :name');
            $countQuery->addParams([':name' => '%' . $searchArray['name'] . '%']);
        }
    
        if (isset($searchArray['id']) && $searchArray['id']) {
            $query->andWhere('auth.test_dial.id = :id');
            $query->addParams([':id' => $searchArray['id']]);
            $countQuery->andWhere('test_dial.id = :id');
            $countQuery->addParams([':id' => $searchArray['id']]);
        }
        
        $data = $query->all();
        $count = $countQuery->count();
        
        return [
            'totalCount' => $count,
            'data' => $data
        ];
    }

    public function actionCall()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $item = $modelName::findOne($this->request['id']);

        if ($item === null) {
            throw new HttpException(404, $this->modelName . ' не найден');
        }

        $server = $this->getServerOr404($item->server_id);

        $apiUrl = 'http://eridanus.mcn.ru:3000/';

        $apiParams = [
            'num_a' => $item->src_number,
            'num_b' => $item->dst_number,
            'hub' => $server->hub_id,
            'troute' => $item->term_trunk_id
        ];

        $request = $apiUrl . 'docall/?' . http_build_query($apiParams);

        $response = json_decode(file_get_contents($request), true);

        if ($response['res'] == 'OK') {
            $item->autocall_uuid = $response['call']['autocall_uuid'];
            $item->save();
        }

        return [
            'item' => $item->toArray(),
            'result' => $response,
            'url' => $request
        ];
    }
}
