<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\classes\PrefixExpander;
use app\exceptions\FormValidationException;
use app\models\billing\BillingDefs;
use app\models\billing\GeoCity;
use app\models\billing\GeoCountry;
use app\models\billing\GeoPrefix;
use app\models\billing\GeoRegion;
use app\models\NetworkConfig;
use app\models\Prefixlist;
use app\models\PrefixlistPrefix;
use app\models\Server;
use Yii;
use yii\helpers\Json;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;

class PrefixlistController extends JsonController
{

    const RESPONSE_STATUS_SUCCESS = 'SUCCESS';
    const RESPONSE_STATUS_ERROR = 'ERROR';

    /**
     * @return \app\models\Prefixlist[]
     * @throws HttpException
     */
    public function actionList()
    {
        if (!\Yii::$app->user->can('prefixlist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        try {
            $server = $this->getServerOr404($this->request['server_id']);
            $hub_id = $server->hub_id > 0 ? $server->hub_id : 0;
            $where = "(server_id in (select id from public.server where hub_id = ".$hub_id.") and sw_shared) or server_id = ".$server->id . " or is_global = true";
        } catch (HttpException $e) {
            $server = $this->getServerOcsOr404($this->request['server_id']);
            $where = "server_id = ".$server->id." or sw_share_with_camel";
        }

        return
            Prefixlist::find()
                ->select(['id', 'name'])
                ->where($where)
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    /**
     * @return \app\models\Prefixlist[]
     * @throws HttpException
     */
    public function actionListByType()
    {
        if (!\Yii::$app->user->can('prefixlist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        return
            Prefixlist::find()
                ->select(['id', 'name'])
                ->where(['type_id' => $this->request['type_id']])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    /**
     * @return \app\models\Prefixlist[]
     * @throws HttpException
     */
    public function actionListEmergency()
    {
        if (!\Yii::$app->user->can('prefixlist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        return Prefixlist::find()
            ->select(['id', 'name'])
            ->where(['is_emergency' => true])
            ->orderBy('name')
            ->asArray()
            ->all();
    }

    /**
     * @return \app\models\Prefixlist[]
     * @throws HttpException
     */
    public function actionListByCamelShared()
    {
        if (!\Yii::$app->user->can('prefixlist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        return
            Prefixlist::find()
                ->select(['id', 'name'])
                ->where(['sw_share_with_camel' => true])
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    /**
     * @return \app\models\Prefixlist[]
     * @throws HttpException
     */
    public function actionListBlocked()
    {
        if (!\Yii::$app->user->can('prefixlist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $server = $this->getServerOr404($this->request['server_id']);
        $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;
        
        return
            Prefixlist::find()
                ->alias('p')
                ->select(['p.id', 'p.name'])
                ->innerJoin(Server::tableName() . ' s', 'p.id = ANY(s.prefixlist_block)')
                ->where("(s.hub_id = :hub_id and sw_shared) or s.id = :server_id or p.is_global = true")
                ->orderBy('name')
                ->addParams([':hub_id' => $hub_id, ':server_id' => $server->id])
                ->asArray()
                ->all();
    }

    /**
     * @return \app\models\Prefixlist[]
     * @throws HttpException
     */
    public function actionRead()
    {
        if (!\Yii::$app->user->can('prefixlist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }

        try {
            $server = $this->getServerOr404($this->request['server_id']);
            $hub_id = $server->hub_id > 0 ? $server->hub_id : 0 ;
            $where = "( server_id in( select id from public.server where hub_id = ".$hub_id.") and sw_shared )  or server_id = ".$server->id;
        } catch (HttpException $e) {
            $server = $this->getServerOcsOr404($this->request['server_id']);
            $where = "server_id = ".$server->id." or sw_share_with_camel";
        }

        return
            Prefixlist::find()
                ->select([
                    'id', 'name', 'type_id','server_id','sw_shared', 'is_global', 'is_emergency',
                    'to_char(dt_update, \'YYYY-MM-DD HH24:MI:SS\') as dt_update',
                    'to_char(dt_prepare, \'YYYY-MM-DD HH24:MI:SS\') as dt_prepare', 'is_auto_update', 'object_comment'
                ])
                ->where($where)
                ->orderBy('name')
                ->asArray()
                ->all();
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionGet()
    {
        if (!\Yii::$app->user->can('prefixlist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $prefixlist = $this->getPrefixlistOr404($this->request['id']);

        return $prefixlist->toArray();
    }

    /**
     * @throws FormValidationException
     * @throws HttpException
     * @throws \yii\db\Exception
     */
    public function actionSave()
    {
        if (!\Yii::$app->user->can('prefixlist_edit') && !\Yii::$app->user->can('prefixlist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $result = [];
        
        $server = $this->getServerOr404($this->request['server_id']);

        if (isset($this->request['id'])) {
            if (!\Yii::$app->user->can('prefixlist_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $prefixlist = $this->getPrefixlistOr404($this->request['id']);
            $result['log'] = ['data_before' => $this->getDataForLog($prefixlist)];
        } else {
            if (!\Yii::$app->user->can('prefixlist_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $prefixlist = Prefixlist::create($server);
            $result['log'] = ['data_before' => []];
        }

        $prefixlist->load($this->request, '');
        if ($prefixlist->type_id == Prefixlist::PREFIXLIST_TYPE_MANUAL) {
            $prefixlist->setManualList($this->request['manual_list']);
        } else {
            $prefixlist->manual_list = null;
        }
        if ($prefixlist->type_id == Prefixlist::PREFIXLIST_TYPE_LOCAL_PREFIXES) {
            $prefixlist->setSmezhnostList($this->request['smezhnost_list']);
        } else {
            $prefixlist->smezhnost_list = null;
            $prefixlist->network_config_id = null;

        }
        if ($prefixlist->type_id == Prefixlist::PREFIXLIST_TYPE_ROSSVYAZ) {
            $prefixlist->setRossvyazOperators($this->request['rossvyaz_operators']);
        } else {
            $prefixlist->rossvyaz_operator_ids = null;
            $prefixlist->rossvyaz_operators = null;
        }

        if ($prefixlist->type_id == Prefixlist::PREFIXLIST_TYPE_NNP) {
            $prefixlist->setNnpFilters($this->request);
        }

        if ($prefixlist->type_id == Prefixlist::PREFIXLIST_TYPE_7800) {
            $prefixlist->setToken($this->request);
        }
    
        if ($prefixlist->type_id == Prefixlist::PREFIXLIST_TYPE_DID_ON_VPBX) {
            $prefixlist->setPbxFilters($this->request);
        }
    
        if ($prefixlist->type_id == Prefixlist::PREFIXLIST_TYPE_FMC) {
            $prefixlist->setFmcFilters($this->request);
        }
    
        if ($prefixlist->type_id == Prefixlist::PREFIXLIST_TYPE_ROAMING) {
            $prefixlist->setTrunkRoamingFilters($this->request);
        }
    
        if ($prefixlist->type_id == Prefixlist::PREFIXLIST_TYPE_VOIP_REGISTRY) {
            $prefixlist->setVoipRegistryFilters($this->request);
        }
    
        if ($prefixlist->type_id == Prefixlist::PREFIXLIST_TYPE_VOIP_NUMBER) {
            $prefixlist->setVoipNumberFilters($this->request);
        }

        if ($prefixlist->type_id == Prefixlist::PREFIXLIST_TYPE_GT) {
            $prefixlist->setGtFilters($this->request);
        }

        if ($prefixlist->type_id == Prefixlist::PREFIXLIST_TYPE_RN) {
            $prefixlist->setRnFilters($this->request);
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

            if ($prefixlist->type_id == 2 && $prefixlist->network_config_id) {
                PrefixlistPrefix::deleteByPrefixlist($prefixlist);
                $networkConfigId = NetworkConfig::findOne($prefixlist->network_config_id)->id;

                $sql = <<<SQL
                            select r.prefix from billing.network_prefix r
                            where r.network_config_id = :networkConfigId
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
                            [':networkConfigId' => $networkConfigId, ':now' => date('Y-m-d')]
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

            if ($prefixlist->type_id == 5) {
                PrefixlistPrefix::deleteByPrefixlist($prefixlist);
                $data = [];
                if ($server->min_price_for_autorouting > 0) {
                    $command =
                        Yii::$app->db->createCommand("
                            select defcode
                            from billing.defs
                            where
                                date_from <= date_trunc('day', now())
                                and date_to >= date_trunc('day', now())
                                and not deleted
                                and price > :maxPrice
                                and pricelist_id in (
                                	select ts.pricelist_id from billing.service_trunk t
                                    inner join billing.service_trunk_settings ts on t.id = ts.trunk_id
                                    where	t.term_enabled and t.server_id=:serverId and
                                            t.activation_dt<now() and t.expire_dt>now() and
                                            ts.type=2
                                )
                            group by defcode
                        ", [':maxPrice' => $server->min_price_for_autorouting, ':serverId' => $server->id]);
                    foreach ($command->queryAll() as $item) {
                        $data[] = [$prefixlist->id, $item['defcode']];
                    }
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
    
        $result['log']['data_after'] = $this->getDataForLog($prefixlist);
        $result['result'] = ['id' => $prefixlist->id];
    
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('prefixlist_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $prefixlist = $this->getPrefixlistOr404($this->request['id']);
        $prefixlist->delete();
    }

    /**
     * @return array
     * @throws HttpException
     */
    public function actionNnpCalculation()
    {
        if (!\Yii::$app->user->can('prefixlist_edit') && !\Yii::$app->user->can('prefixlist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        try {
            $prefixlist = $this->getPrefixlistOr404($this->request['id']);
        } catch (\Exception $e) {
            return [
                'result' => self::RESPONSE_STATUS_ERROR,
                'message' => $e->getMessage(),
            ];
        }

        if (!$prefixlist->type_id == Prefixlist::PREFIXLIST_TYPE_NNP || !$prefixlist->nnp_filter_json) {
            return [
                'result' => self::RESPONSE_STATUS_ERROR,
                'message' => 'Некорректный тип префикслиста или фильтры не установлены',
            ];
        }

        try {
            $filter = Json::decode($prefixlist->nnp_filter_json, $asArray = false);
        } catch (\Exception $e) {
            return [
                'result' => self::RESPONSE_STATUS_ERROR,
                'message' => $e->getMessage(),
            ];
        }

        if (!$filter->nnp_destination_id && !$filter->country_code) {
            return [
                'result' => self::RESPONSE_STATUS_ERROR,
                'message' => 'Некорректные настройки фильтрации',
            ];
        }

        $query = [
            'cmd' => 'fillNNPPrefixList',
            'id' => $prefixlist->id,
            'token' => $filter->token,
        ];

        $request = Yii::$app->params['NnpCalculationApi'] . '?' . http_build_query($query);
        $response = file_get_contents($request);

        try {
            $response = Json::decode($response);
        } catch (\Exception $e) {
            return [
                'result' => self::RESPONSE_STATUS_ERROR,
                'message' => $e->getMessage(),
            ];
        }

        return [
            'response' => self::RESPONSE_STATUS_SUCCESS,
            'message' => $response,
        ];
    }

    public function actionPrefixlistGeneration()
    {
        if (!\Yii::$app->user->can('prefixlist_edit') && !\Yii::$app->user->can('prefixlist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        try {
            $prefixlist = $this->getPrefixlistOr404($this->request['id']);
        } catch (\Exception $e) {
            return [
                'result' => self::RESPONSE_STATUS_ERROR,
                'message' => $e->getMessage(),
            ];
        }

        $nnpFilterArray = json_decode($prefixlist['nnp_filter_json'], true);

        $uri = Yii::$app->params['prefixListTypeSevenGenerateLink'];

        $uri = str_replace('{id}', $this->request['id'], $uri);
        $uri = str_replace('{type}', $this->request['type'], $uri);
        $uri = str_replace('{token}', $nnpFilterArray['token'], $uri);

        $response = file_get_contents($uri);

        if (strpos($response, 'ERROR') !== false) {
            return [
                'response' => self::RESPONSE_STATUS_ERROR,
                'message' => $response
            ];
        }

        return [
            'response' => self::RESPONSE_STATUS_SUCCESS,
            'message' => $response,
        ];
    }
    
    public function actionApplyBuffer()
    {
        if (!\Yii::$app->user->can('prefixlist_edit') && !\Yii::$app->user->can('prefixlist_create')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $command =
            \Yii::$app->db
                ->createCommand("select auth.apply_precompiled_prefixlist(:prefixlistId)", [':prefixlistId' => $this->request['id']]);
        
        $result = $command->queryAll();
        
        if ($result[0]['apply_precompiled_prefixlist'] == -1) {
            return [
                'response' => self::RESPONSE_STATUS_ERROR,
                'message' => 'Ошибка применения буфера',
            ];
        }
    
        return [
            'response' => self::RESPONSE_STATUS_SUCCESS,
            'message' => 'Применено успешно'
        ];
    }
    
    /**
     * @return array
     * @throws HttpException
     */
    public function actionFindUsagesInNumbers()
    {
        if (!\Yii::$app->user->can('prefixlist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $prefixlist = $this->getPrefixlistOr404($this->request['id']);

        return $prefixlist->findUsagesInNumbers();
    }
    
    /**
     * @return array
     * @throws HttpException
     */
    public function actionFindUsagesInTrunkABRules()
    {
        if (!\Yii::$app->user->can('prefixlist_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $prefixlist = $this->getPrefixlistOr404($this->request['id']);
        
        return $prefixlist->findUsagesInTrunkABRules();
    }
}
