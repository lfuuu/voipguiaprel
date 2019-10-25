<?php

namespace app\controllers\json\camel;

use app\classes\JsonController;

class TrunkController extends JsonController
{
    protected $modelName = 'app\models\auth\CamelTrunk';
    protected $idParamName = 'id';
    protected $nameParamName = 'name';
    protected $readWhere = ['server_id'];
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
                ->select(['t.*', 'prefixlist_name' => 'p.name', 'route_table_name' => 'rt.name'])
                ->leftJoin('auth.prefixlist p', 'p.id = t.prefixlist_id')
                ->leftJoin('auth.camel_route_table rt', 'rt.id = t.camel_route_table_id')
                ->orderBy('t.' . $this->nameParamName)
                ->asArray()
                ->all();

        return $items;
    }
}
