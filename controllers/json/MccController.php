<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\nnp\Mcc;
use app\exceptions\FormValidationException;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class MccController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('mcc_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        return
            Mcc::find()
                ->select(['id' => 'mcc', 'mcc' => new Expression('LPAD(mcc::text, 3, \'0\')'), 'name' => 'country'])
                ->orderBy('country')
                ->asArray()
                ->all();
    }

    public function actionRead()
    {
        if (!\Yii::$app->user->can('mcc_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            Mcc::find()
                ->select([new Expression('LPAD(mcc::text, 3, \'0\') as mcc'), 'country', 'iso', 'country_code'])
                ->orderBy('country')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('mcc_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = Mcc::findOne(['mcc' => $this->request['mcc']]);
        
        if ($item === null) {
            throw new HttpException(404, 'MCC не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('mcc_edit') && !\Yii::$app->user->can('mcc_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $result = [];
        
        if (isset($this->request['mcc'])) {
            $item = Mcc::findOne(['mcc' => $this->request['mcc']]);
            
            if (empty($item)) {
                if (!\Yii::$app->user->can('mcc_create')) {
                    throw new ForbiddenHttpException('Access denied');
                }
    
                $item = Mcc::create();
                $result['log'] = ['data_before' => []];
            } else {
                if (!\Yii::$app->user->can('mcc_edit')) {
                    throw new ForbiddenHttpException('Access denied');
                }
    
                $result['log'] = ['data_before' => $this->getDataForLog($item)];
            }
        } else {
            if (!\Yii::$app->user->can('mcc_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $item = Mcc::create();
            $result['log'] = ['data_before' => []];
        }

        $item->load($this->request, '');

        $transaction = Mcc::getDb()->beginTransaction();
        try {
            if (!$item->save()) {
                throw new FormValidationException($item);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    
        $result['log']['data_after'] = $this->getDataForLog($item);
    
        return $result;
    }

    public function actionDelete()
    {
        if (!\Yii::$app->user->can('mcc_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = Mcc::findOne(['mcc' => $this->request['id']]);
        $item->delete();
    }
}
