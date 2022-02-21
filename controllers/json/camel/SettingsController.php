<?php

namespace app\controllers\json\camel;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\auth\CamelGtNumberPreprocessing;

class SettingsController extends JsonController
{
    protected $modelName = 'app\models\ServerOcs';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $readWhere = ['server_id'];
    protected $withDependencies = ['numberPreprocessing'];
    protected $listPermission = 'camel_server_list';
    protected $editPermission = 'camel_server_edit';

    public function actionCreate()
    {
        // do_nothing
    }

    public function actionDelete()
    {
        // do_nothing
    }

    protected function performAfterSaveActions($item, $request)
    {
        CamelGtNumberPreprocessing::deleteByServerId($item);
        if (isset($request['numberPreprocessing'])) {
            $order = 1;
            foreach ($request['numberPreprocessing'] as $ruleData) {
                $rule = CamelGtNumberPreprocessing::create($item, $ruleData);
                $rule->order = $order;
                if (!$rule->save()) {
                    throw new FormValidationException($rule);
                }
                $order++;
            }
        }
    }
}
