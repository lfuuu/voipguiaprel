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
    public function actionList()
    {
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
    public function actionRead()
    {
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
        $item = TestGroup::findOne($this->request['id']);

        if ($item === null) {
            throw new HttpException(404, 'TestGroup не найден');
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
        if (isset($this->request['id'])) {
            $item = $this->getTestGroupOr404($this->request['id']);
        } else {
            $item = TestGroup::create();
        }

        $item->load($this->request, '');

        $transaction = TestGroup::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive()) {
                $transaction->rollBack();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function actionDelete()
    {
        $item = TestGroup::findOne($this->request['id']);
        $item->delete();
    }
}
