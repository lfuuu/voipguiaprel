<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\billing_uu\A2pAlphaNumbers;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class A2pAlphaNumbersController extends JsonController
{
    protected $modelName = 'app\models\billing_uu\A2pAlphaNumbers';
    protected $idParamName = 'id';
    protected $alphanumParamName = 'alphanum';
    
    public function actionGet()
    {
        $item = A2pAlphaNumbers::findOne($this->request['id']);

        if ($item === null) {
            throw new HttpException(404, ' не найден');
        }

        return $item->toArray();
    }

    public function actionList()
    {
        return
            A2PAlphaNumbers::find()
                ->select(['id', 'name' => 'alphanum'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionSave()
    {
        $modelName = $this->modelName;

        $result = [];

        if (isset($this->request[$this->idParamName])) {
            $item = $modelName::findOne($this->request[$this->idParamName]);

            if ($item === null) {
                if ($this->throwExceptionOnEmptyItemInSave) {
                    throw new HttpException(404, $this->modelName . ' не найден');
                } else {
                    $item = $modelName::create();
                    $result['log'] = ['data_before' => []];
                }
            } else {
                $result['log'] = ['data_before' => self::getDataForLog($item)];
            }

        } else {
            $item = $modelName::create();
            $result['log'] = ['data_before' => []];
        }

        $item->load($this->request, '');

        $this->performBeforeSaveActions($item, $this->request);

        $transaction = $modelName::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }

            $this->performAfterSaveActions($item, $this->request);

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }

        $result['log']['data_after'] = self::getDataForLog($item);

        return $result;
    }
}