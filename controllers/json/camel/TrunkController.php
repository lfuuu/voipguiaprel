<?php

namespace app\controllers\json\camel;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\auth\CamelGtRule;
use app\models\auth\CamelTrunk;
use app\models\auth\CamelTrunkNumberPreprocessing;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class TrunkController extends JsonController
{
    protected $modelName = 'app\models\auth\CamelTrunk';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $readWhere = ['server_id'];
    protected $withDependencies = ['numberPreprocessing', 'camelGtRules'];
    protected $createPermission = 'camel_trunk_create';
    protected $listPermission = 'camel_trunk_list';
    protected $editPermission = 'camel_trunk_edit';
    protected $deletePermission = 'camel_trunk_delete';

    public function actionRead()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $items =
            $modelName::find()
                ->alias('t')
                ->select(['t.*', 'route_table_name' => 'rt.name', 'route_table_id' => 'rt.id'])
                ->leftJoin('auth.camel_route_table rt', 'rt.id = t.camel_route_table_id')
                ->orderBy('t.' . $this->nameParamName)
                ->asArray()
                ->all();

        return $items;
    }

    public function actionCopy()
    {
        return CamelTrunkNumberPreprocessing::find()->where(['camel_trunk_id' => $this->request['id']])->all();
    }

    protected function performAfterSaveActions($item, $request)
    {
        CamelTrunkNumberPreprocessing::deleteByTrunk($item);
        if (isset($request['numberPreprocessing'])) {
            $order = 1;
            foreach ($request['numberPreprocessing'] as $ruleData) {
                $rule = CamelTrunkNumberPreprocessing::create($item, $ruleData);
                $rule->order = $order;
                if (!$rule->save()) {
                    throw new FormValidationException($rule);
                }
                $order++;
            }
        }

        CamelGtRule::deleteByTrunk($item);
        if (isset($request['camelGtRules'])) {
            $order = 1;
            foreach ($request['camelGtRules'] as $ruleData) {
                $rule = CamelGtRule::create($item, $ruleData);
                $rule->order = $order;
                if (!$rule->save()) {
                    throw new FormValidationException($rule);
                }
                $order++;
            }
        }
    }
}
