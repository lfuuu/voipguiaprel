<?php

namespace app\controllers\json;

use app\models\auth\ServiceTrunkRouting;
use app\models\TrunkGroup;
use app\models\TrunkGroupItem;
use Yii;
use app\classes\JsonController;
use app\exceptions\FormValidationException;
use yii\db\Expression;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class TrunkGroupController extends JsonController
{

    /**
     * @return TrunkGroup
     * @throws HttpException
     */
    public function actionList() {
        if (!\Yii::$app->user->can('trunk_group_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);

        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;

        return
            TrunkGroup::find()
                ->select(['id', 'name'])
                ->where("(server_id in (select id from public.server where hub_id = :hub_id) and sw_shared) or server_id = :server_id")
                ->orderBy('name')
                ->addParams([':hub_id' => $hub_id, ':server_id' => $server->id])
                ->asArray()
                ->all();
    }
    
    /**
     * @return TrunkGroup
     * @throws HttpException
     */
    public function actionListForMarketplace() {
        if (!\Yii::$app->user->can('trunk_group_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);
        
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;
        
        return
            TrunkGroup::find()
                ->select(['id', 'name'])
                ->where("(server_id in (select id from public.server where hub_id = :hub_id) and sw_shared) or server_id = :server_id")
                ->andWhere('uplink_trunk_group = true')
                ->orderBy('name')
                ->addParams([':hub_id' => $hub_id, ':server_id' => $server->id])
                ->asArray()
                ->all();
    }

    /**
     * @return TrunkGroup
     * @throws HttpException
     */
    public function actionRead() {
        if (!\Yii::$app->user->can('trunk_group_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);

        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;

        return
            TrunkGroup::find()
                ->select(['id', 'name', 'sw_shared', 'server_id', 'object_comment'])
                ->where("(server_id in (select id from public.server where hub_id = :hub_id) and sw_shared) or server_id = :server_id")
                ->orderBy('name')
                ->addParams([':hub_id' => $hub_id, ':server_id' => $server->id])
                ->asArray()
                ->all();
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionGet()
    {
        if (!\Yii::$app->user->can('trunk_group_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item =
            TrunkGroup::find()
                ->alias('tg')
                ->select(['tg.*', new Expression('case when str.id is null then false else true end as used_in_marketplace')])
                ->with(['trunks', 'trunk_groups'])
                ->leftJoin(['str' => ServiceTrunkRouting::tableName()], 'tg.id = ANY(str.trunk_groups)')
                ->where(['tg.id' => $this->request['id']])
                ->asArray()
                ->one();
        if ($item === null) {
            throw new HttpException(404, 'Группа транков не найдена');
        }

        return $item;
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionGetTrunksWithGroupIntoRules()
    {
        if (!\Yii::$app->user->can('trunk_group_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $group = $this->getTrunkGroupOr404($this->request['id']);

        return $group->getTrunksWithGroupIntoRules();
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionGetTrunksWithGroupIntoPriorities()
    {
        if (!\Yii::$app->user->can('trunk_group_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $group = $this->getTrunkGroupOr404($this->request['id']);

        return $group->getTrunksWithGroupIntoPriorities();
    }
    
    /**
     * @return array
     * @throws HttpException
     */
    public function actionGetRouteTablesWithGroup()
    {
        if (!\Yii::$app->user->can('trunk_group_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $group = $this->getTrunkGroupOr404($this->request['id']);
        
        return $group->getRouteTablesWithGroup();
    }
    
    /**
     * @return array
     * @throws HttpException
     */
    public function actionGetOutcomesWithGroup()
    {
        if (!\Yii::$app->user->can('trunk_group_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $group = $this->getTrunkGroupOr404($this->request['id']);
        
        return $group->getOutcomesWithGroup();
    }

    /**
     * @throws FormValidationException
     * @throws HttpException
     * @throws \yii\db\Exception
     */
    public function actionSave()
    {
        if (!\Yii::$app->user->can('trunk_group_edit') && !\Yii::$app->user->can('trunk_group_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('trunk_group_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $trunkGroup = $this->getTrunkGroupOr404($this->request['id']);
        } else {
    
            if (!\Yii::$app->user->can('trunk_group_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            $trunkGroup = TrunkGroup::create($server);
        }

        $trunkGroup->load($this->request, '');

        $transaction = TrunkGroup::getDb()->beginTransaction();
        try {
            if (!$trunkGroup->save()) {
                throw new FormValidationException($trunkGroup);
            }
            
            TrunkGroupItem::deleteByTrunkGroup($trunkGroup);
            foreach ($this->request['trunks'] as $trunkId) {
                $item = TrunkGroupItem::create($trunkGroup, ['trunk_id' => $trunkId]);
                if (!$item->save()) {
                    throw new FormValidationException($trunkGroup);
                }
            }

            foreach ($this->request['trunk_groups'] as $childTrunkGroupId) {
                $item = TrunkGroupItem::create($trunkGroup, ['child_trunk_group_id' => $childTrunkGroupId]);
                if (!$item->save()) {
                    throw new FormValidationException($trunkGroup);
                }
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive()) {
                $transaction->rollBack();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('trunk_group_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item = TrunkGroup::findOne($this->request['id']);
        $item->delete();
    }
}
