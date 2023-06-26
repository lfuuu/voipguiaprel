<?php

namespace app\controllers\json\camel;

use app\classes\JsonController;

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
}
