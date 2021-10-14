<?php

namespace app\controllers\json\sms;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\exceptions\ModelValidationException;
use app\models\billing_uu\A2pAlphaNumberGroup;
use app\models\billing_uu\A2pAlphaNumberListGroup;
use app\models\billing_uu\A2pAlphaNumbers;
use Exception;
use Yii;
use yii\db\Expression;
use yii\db\Query;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class A2pAlphaNumberGroupController extends JsonController
{
    protected $modelName = 'app\models\billing_uu\A2pAlphaNumberGroup';
    protected $idParamName = 'id';
    protected $nameParamName = 'group_name';
    protected $createPermission = 'sms_test_group_create';
    protected $listPermission = 'sms_test_group_list';
    protected $editPermission = 'sms_test_group_edit';
    protected $deletePermission = 'sms_test_group_delete';


    public function actionCopy()
    {
        if (!\Yii::$app->user->can('sms_test_group_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        $transaction = Yii::$app->getDb()->beginTransaction();
        try {
            $newGroup = A2pAlphaNumberGroup::create(['group_name' => $this->request['name'] . ' копия']);
            if (!$newGroup->save()) {
                throw new ModelValidationException($newGroup);
            } 

            $oldAlphaListGroups = A2pAlphaNumberListGroup::findAll(['group_id' => $this->request['id']]);
            foreach ($oldAlphaListGroups as $record) {
                $newRecord = A2pAlphaNumberListGroup::create(['group_id' => (string) $newGroup->id, 'alphanum_list_id' => (string) $record->alphanum_list_id]);
                if (!$newRecord->save()) {
                    throw new ModelValidationException($newRecord);
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e);
            Yii::$app->session->addFlash('error', $e->getMessage());
        }
        
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
            if (isset($this->request['alphanums'])) {
                $upsertResult = $this->_processAlphaNumbers($item);
                if (isset($upsertResult['error'])) {
                    return $upsertResult;
                }
            }

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

        A2pAlphaNumberListGroup::deleteAll(['group_id' => $this->request['id']]);
        $item = $modelName::findOne($this->request[$this->idParamName]);
        $item->delete();
    }

    public function actionListAlphaNumbers()
    {
        return A2pAlphaNumbers::find()
                    ->select('a.alphanum')
                    ->from(A2pAlphaNumbers::tableName() . ' a')
                    ->innerJoin(A2pAlphaNumberListGroup::tableName() . ' ag', 'a.id = ag.alphanum_list_id')
                    ->where(['ag.group_id' => $this->request['id']])
                    ->asArray()
                    ->all();
    }

    private function _processAlphaNumbers($item)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $alphaArray = explode("\n", $this->request['alphanums']);

        $transaction = A2pAlphaNumbers::getDb()->beginTransaction();
        try {
            foreach ($alphaArray as $alphaNum) {
                $alpha = A2pAlphaNumbers::findOne(['alphanum' => $alphaNum]);
                if (!$alpha) {
                    $alpha = A2pAlphaNumbers::create(['alphanum' => $alphaNum]);
                    if (!$alpha->save()) {
                        throw new ModelValidationException($alpha);
                    }
                }

                $listGroup = A2pAlphaNumberListGroup::findOne(['group_id' => $item->id, 'alphanum_list_id' => $alpha->id]);
                if (!$listGroup) {
                    $listGroup = A2pAlphaNumberListGroup::create([
                        'alphanum_list_id' => (string) $alpha->id,
                        'group_id' => (string) $item->id
                    ]);
                    if (!$listGroup->save()) {
                        throw new ModelValidationException($listGroup);
                    }
                }   
            }
            $transaction->commit();
        } catch (Exception $e) {
            $transaction->rollBack(); 
            Yii::error($e);
            Yii::$app->session->addFlash('error', $e->getMessage());
        }
    }
}