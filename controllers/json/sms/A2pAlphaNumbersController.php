<?php

namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\billing_uu\A2pAlphaNumberListGroup;
use app\models\billing_uu\A2pAlphaNumbers;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class A2pAlphaNumbersController extends JsonController
{
    protected $modelName = 'app\models\billing_uu\A2pAlphaNumbers';
    protected $idParamName = 'id';
    protected $nameParamName = 'alphanum';
    protected $createPermission = 'sms_test_group_create';
    protected $listPermission = 'sms_test_group_list';
    protected $editPermission = 'sms_test_group_edit';
    protected $deletePermission = 'sms_test_group_delete';

    public function actionList()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $where = [];

        foreach ($this->readWhere as $param) {
            $where[$param] = $this->request[$param];
        }

        $alphaNums = $modelName::find()
            ->select(['id' => $this->idParamName, 'name' => $this->nameParamName])
            ->orderBy($this->nameParamName)
            ->andWhere($where)
            ->asArray()
            ->all();
        
        $result = [];
        foreach ($alphaNums as $alpha) {
            $groups = A2pAlphaNumberListGroup::find()
                                    ->select('group_id')
                                    ->where(['alphanum_list_id' => $alpha['id']])
                                    ->asArray()
                                    ->all();
            
            if ($groups) {
                foreach ($groups as $group) {
                    $alpha['group_id'][] = $group['group_id'];
                }
                $alpha['group_id'] = implode(',', $alpha['group_id']);
            }

            $result[] = $alpha;
        }
        return $result;
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can($this->editPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;
        $item = $modelName::find()
            ->select($this->getSelect)
            ->with($this->withDependencies)
            ->where([$this->idParamName => $this->request[$this->idParamName]])
            ->asArray()
            ->one();

        if ($item === null) {
            throw new HttpException(404, $modelName . ' не найден');
        }

        $result = [];

        $groups = A2pAlphaNumberListGroup::find()
                                    ->select('group_id')
                                    ->where(['alphanum_list_id' => $item['id']])
                                    ->asArray()
                                    ->all();

        if ($groups) {
            foreach ($groups as $group) {
                $item['group_id'][] = $group['group_id'];
            }
        }
        $result = $item;

        return $result;
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

            $groupIds = $this->request['group_id'];

            if ($groupIds) {
                $alphaListGroup = A2pAlphaNumberListGroup::find()->select('group_id')->where(['group_id' => $groupIds, 'alphanum_list_id' => $item->id])->asArray()->all();
                $newGroups = array_diff($groupIds, $alphaListGroup);
                if ($alphaListGroup) {
                    A2pAlphaNumberListGroup::deleteAll(['alphanum_list_id' => $item->id]);
                }
                foreach ($newGroups as $groupId) {
                    $alphaListGroup = A2pAlphaNumberListGroup::create([
                        'alphanum_list_id' => (string) $item->id,
                        'group_id' => (string) $groupId
                    ]);

                    if (!$alphaListGroup->save()) {
                        throw new FormValidationException($alphaListGroup);
                    }
                    
                }
            } else {
                A2pAlphaNumberListGroup::deleteAll(['alphanum_list_id' => $item->id]);
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

    public function actionDelete()
    {
        if (!\Yii::$app->user->can($this->deletePermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $transaction = $modelName::getDb()->beginTransaction();
        try {
            $allListGroups = A2pAlphaNumberListGroup::find()->where(['alphanum_list_id' => $this->request['id']])->asArray()->all();
            foreach ($allListGroups as $listGroup) {
                if (!$listGroup->delete()) {
                    throw new FormValidationException($listGroup);
                }
            }
            $item = $modelName::findOne($this->request[$this->idParamName]);
            if (!$item->delete()) {
                throw new FormValidationException($item);
            }
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }
}