<?php

namespace app\controllers\json\camel;

use app\classes\JsonController;
use app\models\auth\CamelOutcome;

class OutcomeController extends JsonController
{
    protected $modelName = 'app\models\auth\CamelOutcome';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $readWhere = ['server_id'];
    protected $createPermission = 'camel_outcome_create';
    protected $listPermission = 'camel_outcome_list';
    protected $editPermission = 'camel_outcome_edit';
    protected $deletePermission = 'camel_outcome_delete';

    public function actionGet()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
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

        $item = $this->performAfterGetActions($item);

        return $item;
    }

    public function actionList()
    {   
        $server = $this->getServerOcsOr404($this->request['server_id']);

        $camelList = CamelOutcome::find()
                ->select(['id', 'name'])
                ->where("( server_id in( select id from public.server_ocs where server_id = ".$server->id . '))')
                ->orderBy('name')
                ->asArray()
                ->all();
        
        return $camelList;
    }
}
