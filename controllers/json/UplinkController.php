<?php

namespace app\controllers\json;

use app\classes\JsonController;
use app\exceptions\FormValidationException;
use app\models\billing\ServiceTrunk;
use app\models\nnp\Region;
use app\models\Server;
use app\models\Trunk;
use app\models\Uplink;
use app\models\TrunkABfiltersRule;
use app\models\TrunkNumberPreprocessing;
use app\models\TrunkPriority;
use app\models\TrunkTrunkRule;
use Yii;
use yii\db\StaleObjectException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\db\Expression;

class UplinkController extends JsonController
{

    const LEVEL_HUB = 1;
    const LEVEL_REGION = 2;
    const LEVEL_PHYSICAL_TRUNK = 3;
    const LEVEL_LOGICAL_TRUNK = 4;
    
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
                ->select(['auth.uplink.*',
                    new Expression('case when auth.uplink.active then concat(st.id, \': \', st.id, \', \', \'Вкл\') else concat(st.id, \': \', st.id, \', \', \'Выкл\') end as l_trunk_name'),
                    new Expression('concat(s.id, \': \', s.name) as server_name'),
                    new Expression('case when h.id is not null then concat(h.id, \': \', h.name) else \'Без хаба\' end as hub_name'),
                    new Expression('concat(t.id, \': \', t.trunk_name) as p_trunk_name'),
                    'n_a.name as a_name',
                    'n_b.name as b_name',
                    'h.id as hub_id',
                    's.id as server_id',
                    'st.client_account_id',
                    new Expression('case when vp.is_global = false and vp5.is_global = false then false else true end as pricelist_is_global'),
                    new Expression('case when vp.id is not null then vp.name else case when vp5.id is not null then vp5.name else sts.id::varchar end end as price_name'),
                ])
                ->innerJoin('billing.service_trunk st', 'auth.uplink.l_trunk_id = st.id')
                ->innerJoin('billing.service_trunk_settings sts', 'sts.trunk_id = st.id')
                ->innerJoin('auth.trunk t', 'auth.uplink.p_trunk_id = t.id')
                ->innerJoin('public.server s', 's.id = auth.uplink.region_id')
                ->leftJoin('auth.hub h', 'h.id = s.hub_id')
                ->leftJoin('auth.number n_a', 'n_a.id = sts.src_number_id')
                ->leftJoin('auth.number n_b', 'n_b.id = sts.dst_number_id')
                ->leftJoin('voip.pricelist vp', 'vp.id = sts.pricelist_id')
                ->leftJoin('billing_uu.package_pricelist bpp', 'bpp.tariff_id = sts.nnp_tariff_id')
                ->leftJoin('voip.pricelist vp5', 'vp5.id = bpp.pricelist_id')
                ->orderBy('region_id')
                ->asArray()
                ->all();
        
        $result = [];
        
        foreach ($items as $item) {
            //Для того, чтобы вывести аплинки в виде дерева, нам нужна древесная структура данных.
            $result[$item['hub_name']]['items'][$item['server_name']]['items'][$item['p_trunk_name']]['items'][$item['l_trunk_name']]['items'][] = $item;
            //А для того, чтобы можно было каждый уровень удалять и/или расширять...
            //...у каждого уровня должны быть айдишники.
            $result[$item['hub_name']]['id'] = $item['hub_id'];
            $result[$item['hub_name']]['items'][$item['server_name']]['id'] = $item['server_id'];
            $result[$item['hub_name']]['items'][$item['server_name']]['items'][$item['p_trunk_name']]['id'] = $item['p_trunk_id'];
            $result[$item['hub_name']]['items'][$item['server_name']]['items'][$item['p_trunk_name']]['items'][$item['l_trunk_name']]['id'] = $item['l_trunk_id'];
            $result[$item['hub_name']]['items'][$item['server_name']]['items'][$item['p_trunk_name']]['items'][$item['l_trunk_name']]['client_account_id'] = $item['client_account_id'];
        }
        
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
        
                $pTrunks = Trunk::find()
                    ->where(['server_id' => $params['region_id']])
                    ->all();
        
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
                    ->where($where)
                    ->all();
        
                foreach ($regionList as $region) {
                    $pTrunks = Trunk::find()
                        ->where(['server_id' => $region->id])
                        ->all();
            
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
