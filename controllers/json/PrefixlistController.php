<?php

namespace app\controllers\json;

use app\models\billing\BillingDefs;
use app\models\billing\GeoCity;
use app\models\billing\GeoCountry;
use app\models\billing\GeoPrefix;
use app\models\billing\GeoRegion;
use app\models\Trunk;
use Yii;
use app\models\PrefixlistPrefix;
use app\classes\JsonController;
use app\models\Prefixlist;
use app\classes\PrefixExpander;
use app\exceptions\FormValidationException;
use yii\web\HttpException;

class PrefixlistController extends JsonController
{

    public function actionList() {
        $server = $this->getServerOr404($this->request['server_id']);

        return
            Prefixlist::find()
                ->select(['id', 'name'])
                ->where(['server_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionRead() {
        $server = $this->getServerOr404($this->request['server_id']);

        return
            Prefixlist::find()
                ->select(['id', 'name', 'type_id'])
                ->where(['server_id' => $server->id])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    public function actionGet()
    {
        $item = Prefixlist::findOne($this->request['id']);
        if ($item === null) {
            throw new HttpException(404, 'Список префиксов не найден');
        }

        return $item->toArray();
    }

    public function actionSave()
    {
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            $prefixlist = $this->getPrefixlistOr404($this->request['id']);
        } else {
            $prefixlist = Prefixlist::create($server);
        }

        $prefixlist->load($this->request, '');
        if ($prefixlist->type_id == 1) {
            $prefixlist->setManualList($this->request['manual_list']);
        } else {
            $prefixlist->manual_list = null;
        }
        if ($prefixlist->type_id == 2) {
            $prefixlist->setSmezhnostList($this->request['smezhnost_list']);
        } else {
            $prefixlist->smezhnost_list = null;
            $prefixlist->trunk_id = null;

        }
        if ($prefixlist->type_id == 3) {
            $prefixlist->setRossvyazOperators($this->request['rossvyaz_operators']);
        } else {
            $prefixlist->rossvyaz_operator_ids = null;
            $prefixlist->rossvyaz_operators = null;
        }

        $transaction = Prefixlist::getDb()->beginTransaction();
        try {

            $needSaveRossvyaz = false;

            if ($prefixlist->type_id == 3) {
                if ($prefixlist->rossvyaz_country_id) {
                    if ($country = GeoCountry::findOne($prefixlist->rossvyaz_country_id)) {
                        $prefixlist->rossvyaz_country = $country->name;
                    } else {
                        throw new FormValidationException($prefixlist);
                    }
                }
                if ($prefixlist->rossvyaz_region_id) {
                    if ($region = GeoRegion::findOne($prefixlist->rossvyaz_region_id)) {
                        $prefixlist->rossvyaz_region = $region->name;
                    } else {
                        throw new FormValidationException($prefixlist);
                    }
                }
                if ($prefixlist->rossvyaz_city_id) {
                    if ($city = GeoCity::findOne($prefixlist->rossvyaz_city_id)) {
                        $prefixlist->rossvyaz_city = $city->name;
                    } else {
                        throw new FormValidationException($prefixlist);
                    }
                }
                if ($prefixlist->getDirtyAttributes()) {
                    $needSaveRossvyaz = true;
                }
            }

            if (!$prefixlist->save()) {
                throw new FormValidationException($prefixlist);
            }

            if ($prefixlist->type_id == 1) {
                PrefixlistPrefix::deleteByPrefixlist($prefixlist);
                foreach (PrefixExpander::expand($prefixlist->getManualList()) as $prefixData) {
                    $prefix = PrefixlistPrefix::create($prefixlist, ['prefix' => $prefixData]);
                    if (!$prefix->save()) {
                        throw new FormValidationException($prefix);
                    }
                }
            }

            if ($prefixlist->type_id == 2 && $prefixlist->trunk_id) {
                PrefixlistPrefix::deleteByPrefixlist($prefixlist);
                $trunk = Trunk::findOne($prefixlist->trunk_id);

                $sql = <<<SQL
                            select r.prefix from billing.network_prefix r
                            where r.instance_id = :serverId and r.operator_id = :operatorId
                                and r.deleted = false
                                and r.date_from <= :now
                                and r.date_to >= :now
SQL;
                $smezhnostList = $prefixlist->getSmezhnostList();
                if (!empty($smezhnostList)) {
                    $sql .= ' and r.network_type_id in (' . implode(',', $smezhnostList) . ')';
                }

                $prefixes =
                    BillingDefs::getDb()
                        ->createCommand(
                            $sql,
                            [':serverId' => $server->id, 'operatorId' => $trunk->code, ':now' => date('Y-m-d')]
                        )
                        ->queryAll();

                $data = [];
                foreach ($prefixes as $item) {
                    $data[] = [$prefixlist->id, $item['prefix']];
                }

                if (count($data) > 0) {
                    PrefixlistPrefix::getDb()->createCommand()->batchInsert(PrefixlistPrefix::tableName(),
                        ['prefixlist_id', 'prefix'],
                        $data
                    )->execute();
                }
            }

            if ($prefixlist->type_id == 3 && $needSaveRossvyaz) {
                PrefixlistPrefix::deleteByPrefixlist($prefixlist);
                $query = (new \yii\db\Query())
                    ->select('r.prefix')
                    ->from(['r' => 'geo.prefix'])
                    ->leftJoin(['g' => 'geo.geo'], 'g.id = r.geo_id')
                ;

                if ($prefixlist->rossvyaz_mob === true || $prefixlist->rossvyaz_mob === false) {
                    $query->andWhere(['r.mob' => $prefixlist->rossvyaz_mob]);
                }

                $operatorIds = $prefixlist->getRossvyazOperatorIds();
                if ($operatorIds && !empty($operatorIds)) {
                    if ($prefixlist->exclude_operators) {
                        $query->andWhere(['not', ['r.operator_id' => $operatorIds]]);
                    } else {
                        $query->andWhere(['r.operator_id' => $operatorIds]);
                    }
                }

                if ($prefixlist->rossvyaz_country_id) {
                    $query->andWhere(['g.country' => $prefixlist->rossvyaz_country_id]);
                }
                if ($prefixlist->rossvyaz_region_id) {
                    $query->andWhere(['g.region'=> $prefixlist->rossvyaz_region_id]);
                }
                if ($prefixlist->rossvyaz_city_id) {
                    $query->andWhere(['g.city' => $prefixlist->rossvyaz_city_id]);
                }

                $data = [];
                foreach($query->all(GeoPrefix::getDb()) as $item) {
                    $data[] = [$prefixlist->id, $item['prefix']];
                }

                if (count($data) > 0) {
                    PrefixlistPrefix::getDb()->createCommand()->batchInsert(PrefixlistPrefix::tableName(),
                        ['prefixlist_id', 'prefix'],
                        $data
                    )->execute();
                }
            }

            $prefixlist->count = PrefixlistPrefix::find()->where(['prefixlist_id' => $prefixlist->id])->count();
            if (!$prefixlist->save()) {
                throw new FormValidationException($prefixlist);
            }

            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
    }

    public function actionDelete()
    {
        $item = Prefixlist::findOne($this->request['id']);
        $item->delete();
    }
}
