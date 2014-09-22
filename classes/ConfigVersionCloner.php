<?php

namespace app\classes;

use app\models\Airp;
use app\models\ConfigVersion;
use app\models\Cpc;
use app\models\Number;
use app\models\Operator;
use app\models\OperatorPriority;
use app\models\OperatorRule;
use app\models\Outcome;
use app\models\Prefixlist;
use app\models\PrefixlistPrefix;
use app\models\ReleaseReason;
use app\models\RouteCase;
use app\models\RouteCaseOperator;
use app\models\RouteTable;
use app\models\RouteTableRoute;
use app\models\Trunk;
use app\models\TrunkGroup;

final class ConfigVersionCloner
{
    private $version;
    private $newVersion;
    private $airpMap = array();
    private $cpcMap = array();
    private $releaseReasonMap = array();
    private $prefixlistMap = array();
    private $operatorMap = array();
    private $routeCaseMap = array();
    private $outcomeMap = array();
    private $numberMap = array();
    private $routeTableMap = array();
    private $trunkMap = array();
    private $trunkGroupMap = array();

    public static function create(ConfigVersion $version)
    {
        return new static($version);
    }

    private function __construct(ConfigVersion $version)
    {
        $this->version = $version;
    }


    /**
     * @return ConfigVersion
     */
    public function cloneVersion()
    {
        $transaction = ConfigVersion::getDb()->beginTransaction();
        try {
            $this->newVersion = new ConfigVersion();
            $this->newVersion->server_id = $this->version->server_id;
            $this->newVersion->name = 'Копия ' . $this->version->name;
            $this->newVersion->status_id = ConfigVersion::STATUS_DRAFT;
            $this->newVersion->updated_at = (new \DateTime())->format(\DateTime::ATOM);
            $this->newVersion->calling_station_id_for_line_without_number =
                $this->version->calling_station_id_for_line_without_number;
            $this->newVersion->export_chunk_size = $this->version->export_chunk_size;
            $this->newVersion->min_price_for_autorouting = $this->version->min_price_for_autorouting;
            $this->newVersion->need_recalc_routing_report = true;

            if (!$this->newVersion->save()) {
                throw new \Exception('Не удалось сохранить ConfigVersion');
            };

            $this->cloneAirps();
            $this->cloneCpcs();
            $this->cloneReleaseReasons();
            $this->cloneRouteTables();
            $this->clonePrefixlists();
            $this->cloneTrunks();
            $this->cloneTrunkGroups();
            $this->cloneOperators();
            $this->cloneRouteCases();
            $this->cloneOutcomes();
            $this->cloneNumbers();
            $this->finishCloneRouteTables();

            $this->newVersion->low_balance_outcome_id =
                $this->version->low_balance_outcome_id ? $this->outcomeMap[$this->version->low_balance_outcome_id] : null;
            $this->newVersion->blocked_outcome_id =
                $this->version->blocked_outcome_id ? $this->outcomeMap[$this->version->blocked_outcome_id] : null;
            $this->newVersion->cpc_routing_airp_id =
                $this->version->cpc_routing_airp_id ? $this->airpMap[$this->version->cpc_routing_airp_id] : null;

            if (!$this->newVersion->save(false)) {
                throw new \Exception('Не удалось сохранить ConfigVersion');
            };


            $transaction->commit();
        } finally {
            if ($transaction->getIsActive())
                $transaction->rollBack();
        }
        return $this->newVersion;
    }

    private function cloneTrunks()
    {
        $list = Trunk::find()->configVersion($this->version)->all();
        foreach ($list as $item) {

            $newItem = Trunk::create($this->newVersion);
            $newItem->name = $item->name;
            $newItem->number = $item->number;
            $newItem->full_export = $item->full_export;
            $newItem->cpc_id = $this->cpcMap[$item->cpc_id];
            $newItem->route_table_id = $this->routeTableMap[$item->route_table_id];

            if (!$newItem->save(false)) {
                throw new \Exception('Не удалось сохранить Trunk');
            };

            $this->trunkMap[$item->id] = $newItem->id;
        }
    }

    private function cloneTrunkGroups()
    {
        $list = TrunkGroup::find()->configVersion($this->version)->all();
        foreach ($list as $item) {

            $newItem = TrunkGroup::create($this->newVersion);
            $newItem->name = $item->name;

            $newTrunkIds = [];
            foreach ($item->getTrunks() as $trunkId) {
                $newTrunkIds[] = $this->trunkMap[$trunkId];
            }
            $newItem->setTrunks($newTrunkIds);

            if (!$newItem->save(false)) {
                throw new \Exception('Не удалось сохранить TrunkGroup');
            };

            $this->trunkGroupMap[$item->id] = $newItem->id;
        }
    }

    private function cloneAirps()
    {
        $list = Airp::find()->configVersion($this->version)->all();
        foreach ($list as $item) {

            $newItem = Airp::create($this->newVersion);
            $newItem->name = $item->name;
            if (!$newItem->save(false)) {
                throw new \Exception('Не удалось сохранить AIRP');
            };

            $this->airpMap[$item->id] = $newItem->id;
        }
    }

    private function cloneCpcs()
    {
        $list = Cpc::find()->configVersion($this->version)->all();
        foreach ($list as $item) {

            $newItem = Cpc::create($this->newVersion);
            $newItem->name = $item->name;
            $newItem->value = $item->value;
            if (!$newItem->save(false)) {
                throw new \Exception('Не удалось сохранить CPC');
            };

            $this->cpcMap[$item->id] = $newItem->id;
        }
    }

    private function cloneReleaseReasons()
    {
        $list = ReleaseReason::find()->configVersion($this->version)->all();
        foreach ($list as $item) {

            $newItem = ReleaseReason::create($this->newVersion);
            $newItem->name = $item->name;
            if (!$newItem->save(false)) {
                throw new \Exception('Не удалось сохранить ReleaseReason');
            };

            $this->releaseReasonMap[$item->id] = $newItem->id;
        }
    }

    private function clonePrefixlists()
    {
        $list = Prefixlist::find()->configVersion($this->version)->all();
        foreach ($list as $item) {

            $newItem = Prefixlist::create($this->newVersion);
            $newItem->name = $item->name;
            $newItem->manual_list = $item->manual_list;
            $newItem->type_id = $item->type_id;
            $newItem->rossvyaz_mob = $item->rossvyaz_mob;
            $newItem->rossvyaz_country = $item->rossvyaz_country;
            $newItem->rossvyaz_region = $item->rossvyaz_region;
            $newItem->rossvyaz_city = $item->rossvyaz_city;
            $newItem->rossvyaz_country_id = $item->rossvyaz_country_id;
            $newItem->rossvyaz_region_id = $item->rossvyaz_region_id;
            $newItem->rossvyaz_city_id = $item->rossvyaz_city_id;
            $newItem->rossvyaz_operators = $item->rossvyaz_operators;
            $newItem->rossvyaz_operator_ids = $item->rossvyaz_operator_ids;
            $newItem->count = $item->count;
            if (!$newItem->save(false)) {
                throw new \Exception('Не удалось сохранить Prefixlist');
            };


            $data = [];
            $listPrefix =
                PrefixlistPrefix::find()->select(['prefix'])
                    ->where(['prefixlist_id' => $item->id])
                    ->asArray()
                    ->all()
                ;
            foreach($listPrefix as $prefix) {
                $data[] = [$newItem->id, $prefix['prefix']];
            }

            if (count($data) > 0) {
                PrefixlistPrefix::getDb()->createCommand()->batchInsert(PrefixlistPrefix::tableName(),
                    ['prefixlist_id', 'prefix'],
                    $data
                )->execute();
            }

            $this->prefixlistMap[$item->id] = $newItem->id;
        }
    }

    private function cloneOperators()
    {
        $list = Operator::find()->configVersion($this->version)->all();
        foreach ($list as $item) {

            $newItem = Operator::create($this->newVersion);
            $newItem->code = $item->code;
            $newItem->name = $item->name;
            $newItem->source_rule_default_allowed = $item->source_rule_default_allowed;
            $newItem->destination_rule_default_allowed = $item->destination_rule_default_allowed;
            $newItem->default_priority = $item->default_priority;
            $newItem->openca = $item->openca;
            $newItem->auto_routing = $item->auto_routing;

            if (!$newItem->save(false)) {
                throw new \Exception('Не удалось сохранить Operator');
            };

            $listPriority = OperatorPriority::find()->operator($item)->all();
            foreach($listPriority as $priority) {
                $newPriority = OperatorPriority::create($newItem);
                $newPriority->order = $priority->order;
                $newPriority->priority = $priority->priority;
                $newPriority->prefixlist_id = $this->prefixlistMap[$priority->prefixlist_id];
                if (!$newPriority->save(false)) {
                    throw new \Exception('Не удалось сохранить OperatorPriority');
                };
            }

            $listRule = OperatorRule::find()->operator($item)->all();
            foreach($listRule as $rule) {
                $newRule = OperatorRule::create($newItem);
                $newRule->order = $rule->order;
                $newRule->outgoing = $rule->outgoing;
                $newRule->trunk_group_id = $this->trunkGroupMap[$rule->trunk_group_id];
                $newRule->prefixlist_id = $this->prefixlistMap[$rule->prefixlist_id];
                if (!$newRule->save(false)) {
                    throw new \Exception('Не удалось сохранить OperatorRule');
                };
            }

            $this->operatorMap[$item->id] = $newItem->id;
        }
    }

    private function cloneRouteCases()
    {
        $list = RouteCase::find()->configVersion($this->version)->all();
        foreach ($list as $item) {

            $newItem = RouteCase::create($this->newVersion);
            $newItem->name = $item->name;
            if (!$newItem->save(false)) {
                throw new \Exception('Не удалось сохранить RouteCase');
            };

            $listOperator = RouteCaseOperator::find()->routeCase($item)->all();
            foreach($listOperator as $operator) {
                $newOperator = RouteCaseOperator::create($newItem);
                $newOperator->operator_id = $this->operatorMap[$operator->operator_id];
                $newOperator->priority = $operator->priority;
                $newOperator->weight = $operator->weight;
                if (!$newOperator->save(false)) {
                    throw new \Exception('Не удалось сохранить RouteCaseOperator');
                };
            }

            $this->routeCaseMap[$item->id] = $newItem->id;
        }
    }

    private function cloneOutcomes()
    {
        $list = Outcome::find()->configVersion($this->version)->all();
        foreach ($list as $item) {

            $newItem = Outcome::create($this->newVersion);
            $newItem->name = $item->name;
            $newItem->type_id = $item->type_id;
            $newItem->calling_station_id = $item->calling_station_id;
            $newItem->called_station_id = $item->called_station_id;
            $newItem->route_case_id =
                $item->route_case_id ? $this->routeCaseMap[$item->route_case_id] : null;
            $newItem->release_reason_id =
                $item->release_reason_id ? $this->releaseReasonMap[$item->release_reason_id] : null;
            $newItem->airp_id =
                $item->airp_id ? $this->airpMap[$item->airp_id] : null;
            if (!$newItem->save(false)) {
                throw new \Exception('Не удалось сохранить Outcome');
            };

            $this->outcomeMap[$item->id] = $newItem->id;
        }
    }

    private function cloneNumbers()
    {
        $list = Number::find()->configVersion($this->version)->all();;
        foreach ($list as $item) {

            $newItem = Number::create($this->newVersion);
            $newItem->name = $item->name;
            $newItem->type_id = $item->type_id;
            $newItem->cpc_id = $this->cpcMap[$item->cpc_id];

            $newPrefixlistIds = [];
            foreach ($item->getPrefixlists() as $prefixlistId) {
                $newPrefixlistIds[] = $this->prefixlistMap[$prefixlistId];
            }
            $newItem->setPrefixlists($newPrefixlistIds);
            if (!$newItem->save(false)) {
                throw new \Exception('Не удалось сохранить Number');
            };

            $this->numberMap[$item->id] = $newItem->id;
        }
    }

    private function cloneRouteTables()
    {
        $list = RouteTable::find()->configVersion($this->version)->all();
        foreach ($list as $item) {

            $newItem = RouteTable::create($this->newVersion);
            $newItem->name = $item->name;
            if (!$newItem->save(false)) {
                throw new \Exception('Не удалось сохранить RouteTable');
            };

            $this->routeTableMap[$item->id] = $newItem->id;
        }
    }

    private function finishCloneRouteTables()
    {
        $list = RouteTable::find()->configVersion($this->version)->all();
        foreach ($list as $item) {

            $listRoute = RouteTableRoute::find()->routeTable($item)->all();
            foreach($listRoute as $route) {
                $newRoute = new RouteTableRoute();
                $newRoute->route_table_id = $this->routeTableMap[$route->route_table_id];
                $newRoute->order = $route->order;
                $newRoute->a_number_id = $route->a_number_id ? $this->numberMap[$route->a_number_id] : null;
                $newRoute->b_number_id = $route->b_number_id ? $this->numberMap[$route->b_number_id] : null;
                $newRoute->outcome_id = $route->outcome_id ? $this->outcomeMap[$route->outcome_id] : null;
                $newRoute->outcome_route_table_id = $route->outcome_route_table_id ? $this->routeTableMap[$route->outcome_route_table_id] : null;
                if (!$newRoute->save(false)) {
                    throw new \Exception('Не удалось сохранить RouteTableRoute');
                };
            }

        }
    }
}