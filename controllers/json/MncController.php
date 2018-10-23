<?php

namespace app\controllers\json;

use Yii;
use app\classes\JsonController;
use app\models\nnp\Mnc;
use app\exceptions\FormValidationException;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class MncController extends JsonController
{
    public function actionList()
    {
        if (!\Yii::$app->user->can('mnc_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        return
            Mnc::find()
                ->select(['id' => 'mnc', 'mnc' => new Expression('LPAD(mnc::text, 2, \'0\')'), 'name' => 'network'])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead()
    {
        if (!\Yii::$app->user->can('mnc_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            Mnc::find()
                ->select([new Expression('LPAD(mcc::text, 3, \'0\') as mcc'), new Expression('LPAD(mnc::text, 2, \'0\') as mnc'), 'network'])
                ->orderBy('network')
                ->asArray()
                ->all();
    }
    
    public function actionListByMcc()
    {
        if (!\Yii::$app->user->can('mnc_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $mcc = $this->request['mcc'];
        
        return
            Mnc::find()
                ->select(['id' => 'mnc', 'mnc' => new Expression('LPAD(mnc::text, 2, \'0\')'), 'name' => 'network'])
                ->where('mcc = :mcc')
                ->orderBy('network')
                ->asArray()
                ->addParams([':mcc' => $mcc])
                ->all();
    }

    public function actionGet()
    {
        if (!\Yii::$app->user->can('mnc_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = Mnc::findOne($this->request['mnc']);
        
        if ($item === null) {
            throw new HttpException(404, 'MNC не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        if (!\Yii::$app->user->can('mnc_edit') && !\Yii::$app->user->can('mnc_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        if (isset($this->request['mnc'])) {
            $item = Mnc::findOne($this->request['mnc']);
        
            if (empty($item)) {
                if (!\Yii::$app->user->can('mnc_create')) {
                    throw new ForbiddenHttpException('Access denied');
                }
            
                $item = Mnc::create();
            } else {
                if (!\Yii::$app->user->can('mnc_edit')) {
                    throw new ForbiddenHttpException('Access denied');
                }
            }
        } else {
            if (!\Yii::$app->user->can('mnc_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
        
            $item = Mnc::create();
        }

        $item->load($this->request, '');

        $transaction = Mnc::getDb()->beginTransaction();
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
        if (!\Yii::$app->user->can('mnc_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = Mnc::findOne(['mnc' => $this->request['id']]);
        $item->delete();
    }
}
