<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class CommentController extends JsonController
{
    protected $doNotLog = true;
    
    private $_objectTypes = [
        'trunk' => ['model_name' => '\app\models\Trunk', 'permissions' => ['trunk_edit']],
        'trunk_priority' => ['model_name' => '\app\models\TrunkPriority', 'permissions' => ['trunk_edit']],
        'trunk_numbers_rules' => ['model_name' => '\app\models\TrunkABfiltersRule', 'permissions' => ['trunk_edit']],
        'trunk_rules' => ['model_name' => '\app\models\TrunkTrunkRule', 'permissions' => ['trunk_edit']],
        'trunk_preprocessing' => ['model_name' => '\app\models\TrunkNumberPreprocessing', 'permissions' => ['trunk_edit']],
        'trunk_sorm' => ['model_name' => '\app\models\sorm\Trunk', 'permissions' => ['trunk_edit']],
        'trunk_load_limit' => ['model_name' => '\app\models\TrunkLoadLimit', 'permissions' => ['trunk_edit']],
        'trunk_group' => ['model_name' => '\app\models\TrunkGroup', 'permissions' => ['trunk_group_edit']],
        'imsi_partner' => ['model_name' => '\app\models\billing_uu\ImsiPartner', 'permissions' => ['imsi_partner_edit']],
        'route_table' => ['model_name' => '\app\models\RouteTable', 'permissions' => ['route_table_edit']],
        'number' => ['model_name' => '\app\models\Number', 'permissions' => ['number_edit']],
        'outcome' => ['model_name' => '\app\models\Outcome', 'permissions' => ['outcome_edit']],
        'route_case' => ['model_name' => '\app\models\RouteCase', 'permissions' => ['route_case_edit']],
        'prefixlist' => ['model_name' => '\app\models\Prefixlist', 'permissions' => ['route_case_edit']],
        'oca_bw' => ['model_name' => '\app\models\OcaBw', 'permissions' => ['oca_bw_edit']],
        'airp' => ['model_name' => '\app\models\Airp', 'permissions' => ['airp_edit']],
        'cpc' => ['model_name' => '\app\models\Cpc', 'permissions' => ['cpc_edit']],
        'release_reason' => ['model_name' => '\app\models\ReleaseReason', 'permissions' => ['release_reason_edit']],
        'header' => ['model_name' => '\app\models\auth\Header', 'permissions' => ['header_edit']],
        'header_rule' => ['model_name' => '\app\models\auth\HeaderRule', 'permissions' => ['header_rule_edit']],
        'attribute' => ['model_name' => '\app\models\Attribute', 'permissions' => ['attribute_edit']],
        'attribute_group' => ['model_name' => '\app\models\AttributeGroup', 'permissions' => ['attribute_group_edit']],
        'test_auth' => ['model_name' => '\app\models\TestAuth', 'permissions' => ['test_auth_edit']],
        'test_call' => ['model_name' => '\app\models\TestCall', 'permissions' => ['test_call_edit']],
        'test_group' => ['model_name' => '\app\models\TestGroup', 'permissions' => ['test_group_edit']],
        'trunk_group_item' => ['model_name' => '\app\models\TrunkGroupItem', 'permissions' => ['trunk_group_edit']],
        'route_table_route' => ['model_name' => '\app\models\RouteTableRoute', 'permissions' => ['route_table_edit']],
        'route_table_rule' => ['model_name' => '\app\models\RouteRouteRule', 'permissions' => ['route_table_edit']],
        'camel_gt_rule' => ['model_name' => '\app\models\CamelGtRule'],
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
