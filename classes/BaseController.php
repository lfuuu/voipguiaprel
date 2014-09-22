<?php
namespace app\classes;

use app\components\View;
use app\models\Airp;
use app\models\ConfigVersion;
use app\models\Number;
use app\models\Operator;
use app\models\Outcome;
use app\models\Prefixlist;
use app\models\ReleaseReason;
use app\models\RouteCase;
use app\models\RouteTable;
use app\models\Server;
use app\models\Trunk;
use app\models\TrunkGroup;
use yii\filters\AccessControl;
use yii\web\HttpException;

/**
 * @method View getView()
 */
class BaseController extends \yii\web\Controller
{
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
     * @return Server
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
     * @return ConfigVersion
     */
    protected function getVersionOr404($versionId)
    {
        if (($version = ConfigVersion::findOne($versionId)) === null) {
            throw new HttpException(404, 'Версия конфигурации не найдена');
        }

        $this->getView()->server = $version->server;
        $this->getView()->version = $version;

        return $version;
    }

    /**
     * @return ConfigVersion
     */
    protected function getVersionForUpdateOr404($versionId)
    {
        $item = $this->getVersionOr404($versionId);
        if ($item->status_id != ConfigVersion::STATUS_DRAFT) {
        //    throw new \Exception(404, 'Версия конфигурации доступна только для чтения');
        }

        return $item;
    }

    /**
     * @return Operator
     */
    protected function getOperatorOr404($operatorId)
    {
        $item = Operator::findOne($operatorId);
        if ($item === null || $item->config_version_id != $this->getView()->version->id) {
            throw new HttpException(404, 'Оператор не найден');
        }
        return $item;
    }

    /**
     * @return Prefixlist
     */
    protected function getPrefixlistOr404($prefixlistId)
    {
        $item = Prefixlist::findOne($prefixlistId);
        if ($item === null || $item->config_version_id != $this->getView()->version->id) {
            throw new HttpException(404, 'Список префиксов не найден');
        }
        return $item;
    }

    /**
     * @return RouteCase
     */
    protected function getRouteCaseOr404($routeCaseId)
    {
        $item = RouteCase::findOne($routeCaseId);
        if ($item === null || $item->config_version_id != $this->getView()->version->id) {
            throw new HttpException(404, 'Route Case не найден');
        }
        return $item;
    }

    /**
     * @return Outcome
     */
    protected function getOutcomeOr404($outcomeId)
    {
        $item = Outcome::findOne($outcomeId);
        if ($item === null || $item->config_version_id != $this->getView()->version->id) {
            throw new HttpException(404, 'Outcome не найден');
        }
        return $item;
    }

    /**
     * @return Number
     */
    protected function getNumberOr404($numberId)
    {
        $item = Number::findOne($numberId);
        if ($item === null || $item->config_version_id != $this->getView()->version->id) {
            throw new HttpException(404, 'Номер не найден');
        }
        return $item;
    }

    /**
     * @return Airp
     */
    protected function getAirpOr404($airpId)
    {
        $item = Airp::findOne($airpId);
        if ($item === null || $item->config_version_id != $this->getView()->version->id) {
            throw new HttpException(404, 'AIRP не найден');
        }
        return $item;
    }

    /**
     * @return RouteTable
     */
    protected function getRouteTableOr404($airpId)
    {
        $item = RouteTable::findOne($airpId);
        if ($item === null || $item->config_version_id != $this->getView()->version->id) {
            throw new HttpException(404, 'Таблица маршрутизации не найдена');
        }
        return $item;
    }

    /**
     * @return ReleaseReason
     */
    protected function getReleaseReasonOr404($releaseReasonId)
    {
        $item = ReleaseReason::findOne($releaseReasonId);
        if ($item === null || $item->config_version_id != $this->getView()->version->id) {
            throw new HttpException(404, 'Release reason не найден');
        }
        return $item;
    }

    /**
     * @return TrunkGroup
     */
    protected function getTrunkGroupOr404($trunkGroupId)
    {
        $item = TrunkGroup::findOne($trunkGroupId);
        if ($item === null || $item->config_version_id != $this->getView()->version->id) {
            throw new HttpException(404, 'Группа транков не найдена');
        }
        return $item;
    }

    /**
     * @return Trunk
     */
    protected function getTrunkOr404($trunkId)
    {
        $item = Trunk::findOne($trunkId);
        if ($item === null || $item->config_version_id != $this->getView()->version->id) {
            throw new HttpException(404, 'Транк не найден');
        }
        return $item;
    }

}