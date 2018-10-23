<?php
namespace app\classes;

use app\components\View;
use app\models\Airp;
use app\models\Attribute;
use app\models\AttributeGroup;
use app\models\auth\RouteReplace;
use app\models\auth\TestPricelist;
use app\models\billing_uu\ImsiPartner;
use app\models\billing_uu\Pricelist;
use app\models\billing_uu\PricelistFilterA;
use app\models\billing_uu\PricelistFilterB;
use app\models\billing_uu\PricelistGroup;
use app\models\billing_uu\PricelistLocation;
use app\models\billing_uu\PricelistPrefixPrice;
use app\models\Cpc;
use app\models\Destination;
use app\models\nnp\Mcc;
use app\models\nnp\Mnc;
use app\models\Number;
use app\models\OcaBw;
use app\models\PrefixlistPrefix;
use app\models\TestAuth;
use app\models\TestCall;
use app\models\TestGroup;
use app\models\Trunk;
use app\models\Outcome;
use app\models\Prefixlist;
use app\models\ReleaseReason;
use app\models\RouteCase;
use app\models\RouteTable;
use app\models\Server;
use app\models\TrunkGroup;
use app\models\Hub;
use app\models\InstanceSettings;
use app\models\Uplink;
use yii\filters\AccessControl;
use yii\web\HttpException;

/**
 * @method View getView()
 */
class BaseController extends \yii\web\Controller
{

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => false,
                    ],
                ],
            ],
        ];
    }

    /**
     * @param int $serverId
     * @return Server
     * @throws HttpException
     */
    protected function getServerOr404($serverId)
    {
        if (($server = Server::findOne($serverId)) === null) {
            throw new HttpException(404);
        }

        $this->getView()->server = $server;

        return $server;
    }

    /**
     * @param int $instanceSettingsId
     * @return InstanceSettings
     * @throws HttpException
     */
    protected function getInstanceSettingsOr404($instanceSettingsId)
    {
        if (($instanceSettings = InstanceSettings::findOne($instanceSettingsId)) === null) {
            throw new HttpException(404);
        }

        $this->getView()->server = $instanceSettings;

        return $instanceSettings;
    }
    
    /**
     * @param int $blacklistSettingsId
     * @return BlacklistSettings
     * @throws HttpException
     */
    protected function getBlacklistSettingsOr404($prefixlistId)
    {
        if (($blacklistSettings = Prefixlist::findOne($prefixlistId)) === null) {
            throw new HttpException(404);
        }
        
        return $blacklistSettings;
    }

    /**
     * @param int $trunkId
     * @return Trunk
     * @throws HttpException
     */
    protected function getTrunkOr404($trunkId)
    {
        $item = Trunk::findOne($trunkId);
        if ($item === null) {
            throw new HttpException(404, 'Транк не найден');
        }
        return $item;
    }
    
    /**
     * @param int $id
     * @return Pricelist
     * @throws HttpException
     */
    protected function getPricelistOr404($id)
    {
        $item = Pricelist::findOne($id);
        if ($item === null) {
            throw new HttpException(404, 'Прайслист не найден');
        }
        return $item;
    }
    
    /**
     * @param int $id
     * @return PricelistLocation
     * @throws HttpException
     */
    protected function getPricelistLocationOr404($id)
    {
        $item = PricelistLocation::findOne($id);
        if ($item === null) {
            throw new HttpException(404, 'Местоположение прайслиста не найдено');
        }
        return $item;
    }
    
    /**
     * @param int $id
     * @return PricelistFilterA
     * @throws HttpException
     */
    protected function getPricelistFilterAOr404($id)
    {
        $item = PricelistFilterA::findOne($id);
        if ($item === null) {
            throw new HttpException(404, 'Фильтр A не найден');
        }
        return $item;
    }
    
    /**
     * @param int $id
     * @return PricelistFilterB
     * @throws HttpException
     */
    protected function getPricelistFilterBOr404($id)
    {
        $item = PricelistFilterB::findOne($id);
        if ($item === null) {
            throw new HttpException(404, 'Фильтр B не найден');
        }
        return $item;
    }
    
    /**
     * @param int $id
     * @return PricelistPrefixPrice
     * @throws HttpException
     */
    protected function getPricelistPrefixPriceOr404($id)
    {
        $item = PricelistPrefixPrice::findOne($id);
        if ($item === null) {
            throw new HttpException(404, 'Цена префикса не найдена');
        }
        return $item;
    }
    
    /**
     * @param int $id
     * @return PricelistGroup
     * @throws HttpException
     */
    protected function getPricelistGroupOr404($id)
    {
        $item = PricelistGroup::findOne($id);
        if ($item === null) {
            throw new HttpException(404, 'Группа прайслистов не найдена');
        }
        return $item;
    }
    
    /**
     * @param int $imsiPartnerId
     * @return ImsiPartner
     * @throws HttpException
     */
    protected function getImsiPartnerOr404($imsiPartnerId)
    {
        $item = ImsiPartner::findOne($imsiPartnerId);
        if ($item === null) {
            throw new HttpException(404, 'IMSI партнер не найден');
        }
        return $item;
    }
    
    /**
     * @param int $uplinkId
     * @return Uplink
     * @throws HttpException
     */
    protected function getUplinkOr404($uplinkId)
    {
        $item = Uplink::findOne($uplinkId);
        if ($item === null) {
            throw new HttpException(404, 'Аплинк не найден');
        }
        return $item;
    }

    /**
     * @param int $hubId
     * @return Hub
     * @throws HttpException
     */
    protected function getHubOr404($hubId)
    {
        $item = Hub::findOne($hubId);
        if ($item === null) {
            throw new HttpException(404, 'Хаб не найден');
        }
        return $item;
    }

    /**
     * @param int $groupId
     * @return TrunkGroup
     * @throws HttpException
     */
    protected function getTrunkGroupOr404($groupId)
    {
        $item = TrunkGroup::findOne($groupId);
        if ($item === null) {
            throw new HttpException(404, 'Группа транков не найдена');
        }
        return $item;
    }

    /**
     * @param int $prefixlistId
     * @return Prefixlist
     * @throws HttpException
     */
    protected function getPrefixlistOr404($prefixlistId)
    {
        $item = Prefixlist::findOne($prefixlistId);
        if ($item === null) {
            throw new HttpException(404, 'Список префиксов не найден');
        }
        return $item;
    }
    
    /**
     * @param int $prefixlistId
     * @return PrefixlistPrefix[]
     * @throws HttpException
     */
    protected function getPrefixlistPrefix($prefixlistId)
    {
        $items = PrefixlistPrefix::find()
            ->select(['prefix'])
            ->where(['prefixlist_id' => $prefixlistId])
            ->asArray()
            ->all();
        
        return $items;
    }

    /**
     * @param int $routeCaseId
     * @return RouteCase
     * @throws HttpException
     */
    protected function getRouteCaseOr404($routeCaseId)
    {
        $item = RouteCase::findOne($routeCaseId);
        if ($item === null) {
            throw new HttpException(404, 'Route Case не найден');
        }
        return $item;
    }

    /**
     * @param int $outcomeId
     * @return Outcome
     * @throws HttpException
     */
    protected function getOutcomeOr404($outcomeId)
    {
        $item = Outcome::findOne($outcomeId);
        if ($item === null) {
            throw new HttpException(404, 'Outcome не найден');
        }
        return $item;
    }

    /**
     * @param int $numberId
     * @return Number
     * @throws HttpException
     */
    protected function getNumberOr404($numberId)
    {
        $item = Number::findOne($numberId);
        if ($item === null) {
            throw new HttpException(404, 'Номер не найден');
        }
        return $item;
    }

    /**
     * @param int $destinationId
     * @return Destination
     * @throws HttpException
     */
    protected function getDestinationOr404($destinationId)
    {
        $item = Destination::findOne($destinationId);
        if ($item === null) {
            throw new HttpException(404, 'Направление не найдено');
        }
        return $item;
    }

    /**
     * @param int $airpId
     * @return Airp
     * @throws HttpException
     */
    protected function getAirpOr404($airpId)
    {
        $item = Airp::findOne($airpId);
        if ($item === null) {
            throw new HttpException(404, 'AIRP не найден');
        }
        return $item;
    }
    
    /**
     * @param int $cpcId
     * @return Cpc
     * @throws HttpException
     */
    protected function getCpcOr404($cpcId)
    {
        $item = Cpc::findOne($cpcId);
        if ($item === null) {
            throw new HttpException(404, 'CPC не найден');
        }
        return $item;
    }
    
    /**
     * @param int $id
     * @return Mcc
     * @throws HttpException
     */
    protected function getMccOr404($mcc)
    {
        $item = Mcc::findOne(['mcc' => $mcc]);
        if ($item === null) {
            throw new HttpException(404, 'MCC не найден');
        }
        return $item;
    }
    
    /**
     * @param int $id
     * @return Mnc
     * @throws HttpException
     */
    protected function getMncOr404($mnc)
    {
        $item = Mnc::findOne(['mnc' => $mnc]);
        if ($item === null) {
            throw new HttpException(404, 'MNC не найден');
        }
        return $item;
    }
    
    /**
     * @param int $ocaBwId
     * @return OcaBw
     * @throws HttpException
     */
    protected function getOcaBwOr404($ocaBwId)
    {
        $item = OcaBw::findOne($ocaBwId);
        if ($item === null) {
            throw new HttpException(404, 'Список OCA BW не найден');
        }
        return $item;
    }

    /**
     * @param int $tableId
     * @return RouteTable
     * @throws HttpException
     */
    protected function getRouteTableOr404($tableId)
    {
        $item = RouteTable::findOne($tableId);
        if ($item === null) {
            throw new HttpException(404, 'Таблица маршрутизации не найдена');
        }
        return $item;
    }
    
    /**
     * @param int $tableId
     * @return RouteReplace
     * @throws HttpException
     */
    protected function getRouteReplaceOr404($id)
    {
        $item = RouteReplace::findOne($id);
        if ($item === null) {
            throw new HttpException(404, 'Список подмены пути не найден');
        }
        return $item;
    }
    
    
    /**
     * @param int $releaseReasonId
     * @return ReleaseReason
     * @throws HttpException
     */
    protected function getReleaseReasonOr404($releaseReasonId)
    {
        $item = ReleaseReason::findOne($releaseReasonId);
        if ($item === null) {
            throw new HttpException(404, 'Release reason не найден');
        }
        return $item;
    }

    /**
     * @param int $testCallId
     * @return TestAuth
     * @throws HttpException
     */
    protected function getTestAuthOr404($testCallId)
    {
        $item = TestAuth::findOne($testCallId);
        if ($item === null) {
            throw new HttpException(404, 'TestAuth не найден');
        }
        return $item;
    }

    /**
     * @param int $testCallId
     * @return TestCall
     * @throws HttpException
     */
    protected function getTestCallOr404($testCallId)
    {
        $item = TestCall::findOne($testCallId);
        if ($item === null) {
            throw new HttpException(404, 'TestCall не найден');
        }
        return $item;
    }
    
    /**
     * @param int $id
     * @return TestPricelist
     * @throws HttpException
     */
    protected function getTestPricelistOr404($id)
    {
        $item = TestPricelist::findOne($id);
        if ($item === null) {
            throw new HttpException(404, 'TestPricelist не найден');
        }
        return $item;
    }

    /**
     * @param int $testGroupId
     * @return TestAuth
     * @throws HttpException
     */
    protected function getTestGroupOr404($testGroupId)
    {
        $item = TestGroup::findOne($testGroupId);
        if ($item === null) {
            throw new HttpException(404, 'TestGroup не найден');
        }
        return $item;
    }

    /**
     * @param int $attributeId
     * @return Attribute
     * @throws HttpException
     */
    protected function getAttributeOr404($attributeId)
    {
        $item = Attribute::findOne($attributeId);
        if ($item === null) {
            throw new HttpException(404, 'Свойство не найдено');
        }
        return $item;
    }

    /**
     * @param int $groupId
     * @return AttributeGroup
     * @throws HttpException
     */
    protected function getAttributeGroupOr404($groupId)
    {
        $item = AttributeGroup::findOne($groupId);
        if ($item === null) {
            throw new HttpException(404, 'Группа свойств не найдена');
        }
        return $item;
    }


}