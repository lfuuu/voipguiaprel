<?php
namespace app\classes;

use app\components\View;
use app\models\Airp;
use app\models\Attribute;
use app\models\AttributeGroup;
use app\models\Destination;
use app\models\Number;
use app\models\TestAuth;
use app\models\TestCall;
use app\models\Trunk;
use app\models\Outcome;
use app\models\Prefixlist;
use app\models\ReleaseReason;
use app\models\RouteCase;
use app\models\RouteTable;
use app\models\Server;
use app\models\TrunkGroup;
use app\models\Hub;
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