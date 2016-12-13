<?php

namespace app\controllers\json;

use app\models\TestAuth;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class TestAuthController extends JsonController
{
    public function actionList() {
        $server = $this->getServerOr404($this->request['server_id']);

        return
            TestAuth::find()
                ->select(['id', 'name'])
                ->where(['server_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $server = $this->getServerOr404($this->request['server_id']);

        return
            TestAuth::find()
                ->where(['server_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        $item = TestAuth::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'TestAuth не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            $item = $this->getTestAuthOr404($this->request['id']);
        } else {
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

    public function actionDelete()
    {
        $item = TestAuth::findOne($this->request['id']);
        $item->delete();
    }

    public function actionResult()
    {
        $item = TestAuth::findOne($this->request['id']); /** @var TestAuth $item */
        if ($item === null) {
            throw new HttpException(404, 'TestAuth не найден');
        }

        $request = $item->server->apiUrl . 'test/auth?' . http_build_query([
            'trunk_name' => $item->trunk_name,
            'src_number' => $item->src_number,
            'dst_number' => $item->dst_number,
            'redirect_number' => $item->redirect_number,
            'src_noa' => $item->src_noa,
            'dst_noa' => $item->dst_noa,
        ]);


        $response = file_get_contents($request);
        $response = str_replace("\r", "", $response);
        $response = explode("\n", $response);

        $result = [];
        foreach ($response as $text) {
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

        return [
            'item' => $item->toArray(),
            'result' => $result,
        ];
    }
}
