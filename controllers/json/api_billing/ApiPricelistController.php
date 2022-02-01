<?php

namespace app\controllers\json\api_billing;

use app\classes\JsonController;
use app\models\billing_api\ApiPricelist;
use app\models\billing_api\ApiPricelistItem;

class ApiPricelistController extends JsonController
{
    protected $modelName = 'app\models\billing_api\ApiPricelist';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $withDependencies = ['items'];
    protected $createPermission = 'api_billing_api_pricelist_create';
    protected $listPermission = 'api_billing_api_pricelist_list';
    protected $editPermission = 'api_billing_api_pricelist_edit';
    protected $deletePermission = 'api_billing_api_pricelist_delete';

    public function actionRead()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $where = [];

        foreach ($this->readWhere as $param) {
            $where[$param] = $this->request[$param];
        }

        $items =
            $modelName::find()
                ->select($this->readSelect)
                ->orderBy($this->nameParamName)
                ->andWhere($where)
                ->andWhere(['is_active' => true])
                ->asArray()
                ->all();

        $items = $this->performAfterReadActions($items);

        return $items;
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can($this->editPermission) && !\Yii::$app->user->can($this->createPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $result = [];

        if (isset($this->request[$this->idParamName])) {
            if (!\Yii::$app->user->can($this->editPermission)) {
                throw new ForbiddenHttpException('Access denied');
            }

            $item = $modelName::findOne($this->request[$this->idParamName]);

            if ($item === null) {
                if ($this->throwExceptionOnEmptyItemInSave) {
                    throw new HttpException(404, $this->modelName . ' не найден');
                } else {
                    if (!\Yii::$app->user->can($this->createPermission)) {
                        throw new ForbiddenHttpException('Access denied');
                    }

                    $item = $modelName::create();
                    $result['log'] = ['data_before' => []];
                }
            } else {
                $result['log'] = ['data_before' => self::getDataForLog($item)];
            }

        } else {
            if (!\Yii::$app->user->can($this->createPermission)) {
                throw new ForbiddenHttpException('Access denied');
            }

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

            if (isset($this->request['items'])) {
                foreach ($this->request['items'] as $apiItem) {
                    if (!$currentApiItem = ApiPricelistItem::findOne(['id' => $apiItem['id']])) {
                        $currentApiItem = ApiPricelistItem::create($apiItem, $item);
                    } else {
                        foreach ($apiItem as $field => $value) {
                            $currentApiItem->$field = $value;
                        }
                    }

                    if (!$currentApiItem->save()) {
                        throw new FormValidationException($currentApiItem);
                    }
        
                }
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

    public function actionReadArchive()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $where = [];

        foreach ($this->readWhere as $param) {
            $where[$param] = $this->request[$param];
        }

        $items =
            $modelName::find()
                ->select($this->readSelect)
                ->orderBy($this->nameParamName)
                ->andWhere($where)
                ->andWhere(['is_active' => false])
                ->asArray()
                ->all();

        $items = $this->performAfterReadActions($items);

        return $items;
    }

    public function actionDelete()
    {
        if (!\Yii::$app->user->can($this->deletePermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $item = $modelName::findOne($this->request[$this->idParamName]);
        $item->is_active = false;
        if (!$item->save()) {
            throw new FormValidationException($item);
        }
    }

    public function actionRestore() {
        if (!\Yii::$app->user->can($this->editPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $item = $modelName::findOne($this->request[$this->idParamName]);
        $item->is_active = true;
        if (!$item->save()) {
            throw new FormValidationException($item);
        }
    }
}
