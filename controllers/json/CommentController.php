<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class CommentController extends JsonController
{
    
    private $_objectTypes = [
        'trunk' => ['model_name' => '\app\models\Trunk', 'permissions' => ['trunk_edit']],
        'trunk_priority' => ['model_name' => '\app\models\TrunkPriority', 'permissions' => ['trunk_edit']],
        'trunk_numbers_rules' => ['model_name' => '\app\models\TrunkABfiltersRule', 'permissions' => ['trunk_edit']],
        'trunk_rules' => ['model_name' => '\app\models\TrunkTrunkRule', 'permissions' => ['trunk_edit']],
        'trunk_preprocessing' => ['model_name' => '\app\models\TrunkNumberPreprocessing', 'permissions' => ['trunk_edit']],
        'trunk_sorm' => ['model_name' => '\app\models\sorm\Trunk', 'permissions' => ['trunk_edit']],
        'trunk_load_limit' => ['model_name' => '\app\models\TrunkLoadLimit', 'permissions' => ['trunk_edit']],
    ];
    
    /**
     * @throws FormValidationException
     * @throws HttpException
     * @throws \yii\db\Exception
     */
    public function actionSave()
    {
        $objectId = $this->request['object_id'];
        $objectType = $this->request['object_type'];
        $objectComment = $this->request['object_comment'];
        
        $modelName = $this->_objectTypes[$objectType]['model_name'];
        $permissions = $this->_objectTypes[$objectType]['permissions'];
        
        $allowed = false;
        
        foreach ($permissions as $permission) {
            if (\Yii::$app->user->can($permission)) {
                $allowed = true;
                break;
            }
        }
        
        if (!$allowed) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = $modelName::findOne($objectId);
        
        $transaction = $modelName::getDb()->beginTransaction();
    
        try {
            $item->object_comment = $objectComment;
            
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
}
