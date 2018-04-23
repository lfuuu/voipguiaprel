<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\models\billing\ServiceTrunk;
use app\models\Server;
use app\models\Trunk;
use app\models\Uplink;
use yii\base\Exception;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\db\Expression;

class UplinkController extends JsonController
{

    const LEVEL_HUB = 1;
    const LEVEL_REGION = 2;
    const LEVEL_PHYSICAL_TRUNK = 3;
    const LEVEL_LOGICAL_TRUNK = 4;
    
    const ACTIVE_MODE_ALL = 1;
    const ACTIVE_MODE_INC = 2;
    const ACTIVE_MODE_EXC = 3;
    
    const TYPE_ORIGINATION = 1;
    const TYPE_TERMINATION = 2;
    
    private $_regionIds = [];
    
    /**
     * @return \app\models\Uplink[]
     * @throws HttpException
     */
    public function actionList()
    {
        if (!\Yii::$app->user->can('uplink_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        return
            Uplink::find()
                ->orderBy('region_id')
                ->asArray()
                ->all();
    }
    
    /**
     * @return \app\models\Uplink[]
     * @throws HttpException
     */
    public function actionRead()
    {
        if (!\Yii::$app->user->can('uplink_list')) {
            throw new ForbiddenHttpException('Access denied');
        }
    
        $items = Uplink::find()
            ->select([
                'auth.uplink.*',
                new Expression('case when auth.uplink.active then concat(st.id, \': \', st.id, \', \', \'Вкл\') else concat(st.id, \': \', st.id, \', \', \'Выкл\') end as l_trunk_name'),
                new Expression('concat(s.id, \': \', s.name) as server_name'),
                new Expression('case when h.id is not null then concat(h.id, \': \', h.name) else \'Без хаба\' end as hub_name'),
                new Expression('concat(t.id, \': \', t.trunk_name) as p_trunk_name'),
                'n_a.name as a_name',
                'n_a.id as a_id',
                'n_b.name as b_name',
                'n_b.id as b_id',
                'h.id as hub_id',
                's.id as server_id',
                'st.client_account_id',
                new Expression('case when vp.is_global = false then false else true end as pricelist_is_global'),
                new Expression('case when vp.id is not null then vp.name else case when bp.tariff_id is not null then bp.name else sts.id::varchar end end as price_name'),
                'auth.uplink.active as uplink_active',
                'sts.id price_name_basic',
                's.name as server_name_basic',
                new Expression('case when h.id is not null then h.name else \'Без хаба\' end as hub_name_basic'),
                't.trunk_name as p_trunk_name_basic',
                'st.id as l_trunk_name_basic'
            ])
            ->innerJoin('billing.service_trunk st', 'auth.uplink.l_trunk_id = st.id')
            ->innerJoin('billing.service_trunk_settings sts', 'sts.trunk_id = st.id')
            ->innerJoin('auth.trunk t', 'auth.uplink.p_trunk_id = t.id')
            ->innerJoin('public.server s', 's.id = auth.uplink.region_id')
            ->leftJoin('auth.hub h', 'h.id = s.hub_id')
            ->leftJoin('auth.number n_a', 'n_a.id = sts.src_number_id')
            ->leftJoin('auth.number n_b', 'n_b.id = sts.dst_number_id')
            ->leftJoin('voip.pricelist vp', 'vp.id = sts.pricelist_id')
            ->leftJoin('billing_uu.package bp', 'bp.tariff_id = sts.nnp_tariff_id')
            ->where('sts.type = ' . self::TYPE_TERMINATION)
            ->andWhere('sts.pricelist_id is not null or sts.nnp_tariff_id is not null')
            ->orderBy('region_id, l_trunk_id, sts.order')
            ->indexBy('price_name_basic')
            ->asArray()
            ->all();
        
        $result = [];
        
        $regionHubs = $this->getRegionHubIds();
        
        foreach ($items as $item) {
            $del = array(' ', ',', ';', '.', "\n");
    
            $regionFilter = explode($del[0], str_replace($del, $del[0], $item['region_filter']));
            
            $regionIds = $this->getRegionIds($item['hub_id']);
            
            switch ($item['active_mode']) {
                case self::ACTIVE_MODE_ALL:
                    $filteredRegionIds = $regionIds;
                    
                    break;
                case self::ACTIVE_MODE_INC:
                    if (empty($regionFilter)) {
                        $filteredRegionIds = [];
                    } else {
                        $filteredRegionIds = array_intersect($regionIds, $regionFilter);
                    }
                    
                    break;
                case self::ACTIVE_MODE_EXC:
                    if (empty($regionList)) {
                        $filteredRegionIds = $regionIds;
                    } else {
                        $filteredRegionIds = array_diff($regionIds, $regionFilter);
                    }
                    
                    break;
                default:
                    throw new Exception();
                    break;
            }
            
            $item['has_road'] = true;
            
            if (is_array($filteredRegionIds) && !empty($filteredRegionIds)) {
                foreach ($filteredRegionIds as $regionId) {
                    $hubId = $regionHubs[$regionId];
                    
                    if ($hubId) {
                        $where = "((server_id in (select id from public.server where hub_id = ".$hubId.") and sw_shared) or server_id = ".$regionId.")";
                    } else {
                        $where = ["server_id" => $regionId];
                    }
                    
                    $hasRoad = Trunk::find()
                        ->where($where)
                        ->andWhere('road_to_regions like \'%' . $item['region_id'] . '\'')
                        ->exists();
                    
                    if (!$hasRoad) {
                        $item['has_road'] = false;
                        $item['road_errors'][$regionId] = '[' . $regionId . ' => ' . $item['region_id'] . ']';
                    }
                }
                
                if (!empty($item['road_errors'])) {
                    ksort($item['road_errors']);
                    $item['road_errors'] = "Нет пути: " . implode(", ", $item['road_errors']);
                }
            }

            if ($this->request['as_tree']) {
                //Для того, чтобы вывести аплинки в виде дерева, нам нужна древесная структура данных.
                $result[$item['hub_name']]['items'][$item['server_name']]['items'][$item['p_trunk_name']]['items'][$item['l_trunk_name']]['items'][] = $item;
                //А для того, чтобы можно было каждый уровень удалять и/или расширять...
                //...у каждого уровня должны быть айдишники.
                $result[$item['hub_name']]['id'] = $item['hub_id'];
                $result[$item['hub_name']]['items'][$item['server_name']]['id'] = $item['server_id'];
                $result[$item['hub_name']]['items'][$item['server_name']]['items'][$item['p_trunk_name']]['id'] = $item['p_trunk_id'];
                $result[$item['hub_name']]['items'][$item['server_name']]['items'][$item['p_trunk_name']]['asr_acd_items'] = ['subitems' => []];
                $result[$item['hub_name']]['items'][$item['server_name']]['items'][$item['p_trunk_name']]['items'][$item['l_trunk_name']]['id'] = $item['l_trunk_id'];
                $result[$item['hub_name']]['items'][$item['server_name']]['items'][$item['p_trunk_name']]['items'][$item['l_trunk_name']]['uplink_active'] = $item['uplink_active'];
                $result[$item['hub_name']]['items'][$item['server_name']]['items'][$item['p_trunk_name']]['items'][$item['l_trunk_name']]['client_account_id'] = $item['client_account_id'];
            }
            else {
                //Тут то же самое, только на уровень меньше.
                $result[$item['hub_name_basic']]['items'][$item['server_name_basic']]['items'][$item['p_trunk_name_basic']]['items'][] = $item;
                $result[$item['hub_name_basic']]['id'] = $item['hub_id'];
                $result[$item['hub_name_basic']]['items'][$item['server_name_basic']]['id'] = $item['server_id'];
                $result[$item['hub_name_basic']]['items'][$item['server_name_basic']]['items'][$item['p_trunk_name_basic']]['id'] = $item['p_trunk_id'];
            }
        }
        
        return $result;
    }
    
    private function getRegionHubIds()
    {
        $items = Server::find()
            ->select(['id', 'hub_id'])
            ->asArray()
            ->all();
        
        $result = [];
        
        foreach ($items as $item) {
            $result[$item['id']] = $item['hub_id'];
        }
        
        return $result;
    }
    
    private function getRegionIds($hubId)
    {
        if (empty($hubId)) {
            $where = 'hub_id is not null';
            $params = [];
            $id = 'none';
        } else {
            $where = 'hub_id <> :hub_id';
            $params = [':hub_id' => $hubId];
            $id = $hubId;
        }
        
        if (isset($this->_regionIds[$id])) {
            return $this->_regionIds[$id];
        }
        
        $servers = Server::find()
            ->select(['id'])
            ->where($where)
            ->addParams($params)
            ->asArray()
            ->all();
        
        $result = [];
        
        foreach ($servers as $server) {
            $result[] = $server['id'];
        }
        
        $this->_regionIds[$id] = $result;
        
        return $result;
    }
    
    /**
     * @return array
     * @throws HttpException
     */
    public function actionGet()
    {
        if (!\Yii::$app->user->can('uplink_edit')) {
            throw new ForbiddenHttpException('Access denied');
        }
        
        $item =
            Uplink::find()
                ->select([
                    'auth.uplink.*',
                    new Expression('case when s.hub_id is not null then s.hub_id else 0 end as hub_id'),
                ])
                ->innerJoin('public.server s', 's.id = auth.uplink.region_id')
                ->where(['l_trunk_id' => $this->request['id']])
                ->asArray()
                ->one();
        
        if ($item === null) {
            throw new HttpException(404, 'Аплинк не найден');
        }
        
        return $item;
    }

    /**
     * @return array
     */
    public function actionSave()
    {
        $params = $this->request;
        
        if (empty($params['id'])) {
            if (!\Yii::$app->user->can('uplink_create')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            if (!empty($params['l_trunk_id'])) {
                unset($params['hub_id']);
                $uplink = Uplink::create($params);
        
                $uplink->save();
            } elseif (!empty($params['p_trunk_id'])) {
                unset($params['hub_id']);
                $lTrunks = ServiceTrunk::findActualByTrunkId($params['p_trunk_id']);
        
                foreach ($lTrunks as $lTrunk) {
                    $data = $params;
                    $data['l_trunk_id'] = $lTrunk->id;
            
                    $uplink = Uplink::create($data);
            
                    $uplink->save();
                }
        
            } elseif (!empty($params['region_id'])) {
                unset($params['hub_id']);
    
                $pTrunks = $this->getActualTrunkList($params['region_id']);
    
                foreach ($pTrunks as $pTrunk) {
                    $lTrunks = ServiceTrunk::findActualByTrunkId($pTrunk->id);
            
                    foreach ($lTrunks as $lTrunk) {
                        $data = $params;
                        $data['p_trunk_id'] = $pTrunk->id;
                        $data['l_trunk_id'] = $lTrunk->id;
                
                        $uplink = Uplink::create($data);
                
                        $uplink->save();
                    }
                }
        
            } elseif (!empty($params['hub_id'])) {
                if ($params['hub_id'] == 'none') {
                    $where = 'hub_id is null';
                } else {
                    $where = ['hub_id' => $params['hub_id']];
                }
        
                unset($params['hub_id']);
        
                $regionList = Server::find()
                    ->select(['id'])
                    ->where($where)
                    ->all();
        
                foreach ($regionList as $region) {
                    $pTrunks = $this->getActualTrunkList($region->id);
            
                    foreach ($pTrunks as $pTrunk) {
                        $lTrunks = ServiceTrunk::findActualByTrunkId($pTrunk->id);
                
                        foreach ($lTrunks as $lTrunk) {
                            $data = $params;
                            $data['p_trunk_id'] = $pTrunk->id;
                            $data['l_trunk_id'] = $lTrunk->id;
                            $data['region_id'] = $region->id;
                    
                            $uplink = Uplink::create($data);
                    
                            $uplink->save();
                        }
                    }
                }
            }
        } else {
            if (!\Yii::$app->user->can('uplink_edit')) {
                throw new ForbiddenHttpException('Access denied');
            }
            
            $uplink = $this->getUplinkOr404($params['id']);
            
            $uplink->active = $params['active'];
            $uplink->active_mode = $params['active_mode'];
            $uplink->region_filter = $params['region_filter'];
            
            $uplink->save();
        }
        
        return ['success' => 1];
    }
    
    private function getActualTrunkList($regionId)
    {
        return Trunk::find()
            ->select(['trunk.id'])
            ->innerJoin('billing.service_trunk st', 'st.trunk_id = auth.trunk.id')
            ->where(['trunk.server_id' => $regionId])
            ->andWhere('auth.trunk.our_trunk = false')
            ->andWhere('st.activation_dt < now()')
            ->andWhere('st.expire_dt > now()')
            ->andWhere('st.term_enabled = true')
            ->all();
    }

    /**
     * @return array
     */
    public function actionDelete()
    {
        if (!\Yii::$app->user->can('uplink_delete')) {
            throw new ForbiddenHttpException('Access denied');
        }

        $id = $this->request['id'];
        $level = $this->request['level'];
        
        switch($level) {
            case self::LEVEL_HUB:
                if (empty($id)) {
                    $regionList = Server::find()
                        ->select('id')
                        ->where('hub_id is null')
                        ->asArray()
                        ->all();
                } else {
                    $regionList = Server::find()
                        ->select('id')
                        ->where(['hub_id' => $id])
                        ->asArray()
                        ->all();
                }
                
                foreach ($regionList as $region) {
                    Uplink::deleteByRegionId($region['id']);
                }
                
                break;
            case self::LEVEL_REGION:
                Uplink::deleteByRegionId($id);
                
                break;
            case self::LEVEL_PHYSICAL_TRUNK:
                Uplink::deleteByPTrunkId($id);
                
                break;
            case self::LEVEL_LOGICAL_TRUNK:
                Uplink::deleteBylTrunkId($id);
                
                break;
            default:
                //do_nothing
                break;
        }
    
        return ['success' => 1];
    }
}
