<?php

namespace app\controllers\json\camel;

use app\classes\JsonController;

class GtController extends JsonController
{
    protected $modelName = 'app\models\auth\CamelGt';
    protected $idParamName = 'id';
    protected $nameParamName = 'gt';
    protected $createPermission = 'camel_gt_create';
    protected $listPermission = 'camel_gt_list';
    protected $editPermission = 'camel_gt_edit';
    protected $deletePermission = 'camel_gt_delete';

    public function actionRead()
    {
        if (!\Yii::$app->user->can($this->listPermission)) {
            throw new ForbiddenHttpException('Access denied');
        }

        $modelName = $this->modelName;

        $items =
            $modelName::find()
                ->alias('gt')
                ->select(['gt.*', 'country_name' => 'c.name_rus', 'region_name' => 'r.name', 'operator_name' => 'o.name'])
                ->leftJoin('nnp.country c', 'c.code = gt.country_code')
                ->leftJoin('nnp.region r', 'r.id = gt.region_id')
                ->leftJoin('nnp.operator o', 'o.id = gt.operator_id')
                ->orderBy($this->nameParamName)
                ->asArray()
                ->all();

        return $items;
    }

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
