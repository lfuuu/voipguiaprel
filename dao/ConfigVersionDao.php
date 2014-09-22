<?php
namespace app\dao;

use app\classes\Assert;
use app\classes\Singleton;
use app\models\Airp;
use app\models\ConfigVersion;
use app\models\Cpc;
use app\models\Number;
use app\models\Operator;
use app\models\Outcome;
use app\models\Prefixlist;
use app\models\ReleaseReason;
use app\models\RouteCase;
use app\models\RouteTable;
use app\models\RouteTableRoute;
use app\models\Trunk;
use app\models\TrunkGroup;

/**
 * @method static ConfigVersionDao me($args = null)
 * @property
 */
class ConfigVersionDao extends Singleton
{
    public function activate(ConfigVersion $version)
    {
        if ($version->status_id == ConfigVersion::STATUS_ACTIVE) {
            Assert::isUnreachable('Нельзя активировать не зафиксированную версию конфигурации');
        }

        $transaction = ConfigVersion::getDb()->beginTransaction();
        try {
            $activeVersions =
                ConfigVersion::find()
                    ->serverId($version->server_id)
                    ->active()
                    ->all();
            foreach ($activeVersions as $tmpVersion) {
                $tmpVersion->status_id = ConfigVersion::STATUS_PUBLISHED;
                $tmpVersion->save();
            }
            $version->status_id = ConfigVersion::STATUS_ACTIVE;
            $version->activated_at = (new \DateTime())->format(\DateTime::ATOM);

            $version->save();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function fix(ConfigVersion $version)
    {
        if ($version->status_id != ConfigVersion::STATUS_DRAFT) {
            Assert::isUnreachable('Зафиксровать можно только версию конфигурации в статусе Черновик');
        }

        if ($version->need_recalc_routing_report) {
            ConfigVersion::getDb()
                ->createCommand('select * from auth.select_routing_report(:versionId, true)', [':versionId' => $version->id])
                ->execute();
            $version->need_recalc_routing_report = false;
        }

        $version->status_id = ConfigVersion::STATUS_PUBLISHED;
        $version->updated_at = (new \DateTime())->format(\DateTime::ATOM);
        $version->save();
    }

    public function delete(ConfigVersion $version)
    {
        if ($version->status_id == ConfigVersion::STATUS_ACTIVE) {
            Assert::isUnreachable('Нельзя удалить активную версию конфигурации');
        }

        $transaction = ConfigVersion::getDb()->beginTransaction();
        try {
            $version->low_balance_outcome_id = null;
            $version->blocked_outcome_id = null;
            $version->cpc_routing_airp_id = null;
            $version->save();

            foreach (
                RouteTable::find()
                    ->configVersion($version)
                    ->select('id')
                    ->asArray()
                    ->all()
                as $route
            ) {
                RouteTableRoute::deleteAll(['route_table_id' => $route['id']]);
            }

            RouteTable::deleteAll(['config_version_id' => $version->id]);

            Outcome::deleteAll(['config_version_id' => $version->id]);

            Number::deleteAll(['config_version_id' => $version->id]);

            Airp::deleteAll(['config_version_id' => $version->id]);

            Cpc::deleteAll(['config_version_id' => $version->id]);

            ReleaseReason::deleteAll(['config_version_id' => $version->id]);

            RouteCase::deleteAll(['config_version_id' => $version->id]);

            Operator::deleteAll(['config_version_id' => $version->id]);

            Prefixlist::deleteAll(['config_version_id' => $version->id]);

            TrunkGroup::deleteAll(['config_version_id' => $version->id]);

            Trunk::deleteAll(['config_version_id' => $version->id]);

            $version->delete();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }
}