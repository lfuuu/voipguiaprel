<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\models\TestGroup;
use yii\web\HttpException;

class TestGroupController extends JsonController
{

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws HttpException
     */
    public function actionList() {
        $server = $this->getServerOr404($this->request['server_id']);

        return
            TestGroup::find()
                ->select(['id', 'name'])
                ->orderBy('id')
                ->asArray()
                ->all();
    }

    /**
     * @return array|\yii\db\ActiveRecord[]
     * @throws HttpException
     */
    public function actionRead() {
        $server = $this->getServerOr404($this->request['server_id']);

        return
            TestGroup::find()
                ->select(['id', 'name'])
                ->orderBy('id')
                ->asArray()
                ->all();
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionGet()
    {
        $item = TestGroup::findOne($this->request['id'])
            ->asArray();

        if ($item === null) {
            throw new HttpException(404, 'TestGroup не найден');
        }

        return $item;
    }
}
