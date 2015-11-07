<?php

namespace app\classes;

use Yii;
use app\models\Airp;
use app\models\Trunk;
use app\models\Number;
use app\models\Outcome;
use app\models\PrefixlistPrefix;
use app\models\ReleaseReason;
use app\models\RouteCase;
use app\models\RouteCaseTrunk;
use app\models\RouteTableRoute;
use app\models\Server;

class ConfigExporter
{
    /** @var Server */
    private $server;

    /** @var Trunk */
    private $trunk;


    private $airpKeys = [];
    private $releaseReasonScripts = [];
    private $routeCaseScripts = [];
    private $airpScripts = [];
    private $autoAirpName = '';


    /**
     * @return ConfigExporter
     */
    public static function create(Trunk $trunk = null, Server $server) {
        return new self($trunk, $server);
    }

    protected function __construct(Trunk $trunk = null, Server $server)
    {
        $this->trunk = $trunk;
        $this->server = $server;
    }

    public function export()
    {
        $airpScenScripts = $this->getRoutes($this->trunk->route_table_id);
        $airpScenScripts['script'][] =
            'UPD-AIRP: AIRP=' . $airpScenScripts['lastAirp'] . ';'. "\n";

        $this->exportAutoRouteCases();

        header('Content-Type: text/plain');

        if (!empty($this->routeCaseScripts)) {
            echo "#### Auto Route Cases ####\n";
            foreach ($this->routeCaseScripts as $script) {
                echo $script['script'];
            }
        }

        if (!empty($this->airpScripts)) {
            foreach ($this->airpScripts as $script) {
                echo $script['script'];
            }
        }

        if (!empty($airpScenScripts['script'])) {
            foreach ($airpScenScripts['script'] as $script) {
                echo $script;
            }
        }
        exit;
    }

    public function exportRC()
    {

        $this->exportAutoRouteCases();

        header('Content-Type: text/plain');

        if (!empty($this->routeCaseScripts)) {
            echo "#### Auto Route Cases ####\n";
            foreach ($this->routeCaseScripts as $script) {
                echo $script['script'];
            }
        }

        exit;
    }

    public function exportNumber($numberId, $airpBaseName, $outcomeScript, $outcomeNext)
    {
        $airpScenScripts = $this->getRoutesNumber($numberId, $airpBaseName, $outcomeScript);
        $airpScenScripts['script'][] =
            'ADD-AIRP-SCEN: AIRP=' . $airpScenScripts['lastAirp'] . ', SCEN=Default, CRITERIA="CDPN=.", OUTCOME="' . $outcomeNext . '";' . "\n";
        $airpScenScripts['script'][] =
            'UPD-AIRP: AIRP=' . $airpScenScripts['lastAirp'] . ';'. "\n";

        header('Content-Type: text/plain');

        if (!empty($this->airpScripts)) {
            foreach ($this->airpScripts as $script) {
                echo $script['script'];
            }
        }

        if (!empty($airpScenScripts['script'])) {
            foreach ($airpScenScripts['script'] as $script) {
                echo $script;
            }
        }
        exit;
    }


    private function getRoutes($routeTableId, $pathRoute = '')
    {
        $firstAirp = '';
        $previousAirpName = '';
        $previousAirpCDNP = '.';
        $previousAirpArray = [];
        $previousSubRouteTable = false;
        $airpScenScripts = [];

        $routeList = RouteTableRoute::find()->where(['route_table_id' => $routeTableId])->orderBy('order')->all();
        $nRoute = 1;
        foreach ($routeList as $route) {

            $nRoute++;

            $aNumber = Number::findOne($route->a_number_id);
            $bNumber = Number::findOne($route->b_number_id);

            if ($route->outcome_id) {
                $outcome = Outcome::findOne($route->outcome_id);
                if ($outcome->type_id == Outcome::TYPE_ROUTE_CASE) {
                    $outcomeScript = $this->exportRouteCase($outcome->route_case_id);
                } elseif ($outcome->type_id == Outcome::TYPE_AIRP) {
                    $outcomeScript = $this->exportAirp($outcome->airp_id);
                } elseif ($outcome->type_id == Outcome::TYPE_RELEASE_REASON) {
                    $outcomeScript = $this->exportReleaseReason($outcome->release_reason_id);
                } elseif ($outcome->type_id == Outcome::TYPE_AUTO) {
                    $previousSubRouteTable = $this->getRoutesAuto($pathRoute . $nRoute . '_');
                    $outcomeScript = 'AIRP=' . $previousSubRouteTable['firstAirp'];
                } else {
                    throw new \Exception("Bad outcome");
                }
            } elseif ($route->outcome_route_table_id) {
                $subRouteTable = $this->getRoutes($route->outcome_route_table_id, $pathRoute . $nRoute . '_');
                $outcomeScript = 'AIRP=' . $subRouteTable['firstAirp'];
            } else {
                throw new \Exception("Bad Route");
            }

            $cpcName = '';
            $airpBaseName = $this->spawnAirpKey($bNumber->name, $cpcName);
            $this->exportAirpLabel($airpBaseName);

            $prefixGroups = $this->getNumberPrefixGroups($route->b_number_id);
            foreach ($prefixGroups as $key => $prefixGroup) {
                $airpBaseScenName = $airpBaseName . (count($prefixGroups) > 1 ? '_' . $key : '');
                $airpName = $pathRoute . $nRoute . '_' . $this->server->id . '_' . $airpBaseScenName;

                if (!$firstAirp) {
                    $firstAirp = $airpName;
                }

                $this->exportAirpByName($airpName);

                if ($previousAirpName) {
                    $airpScenScripts[] =
                        'ADD-AIRP-SCEN: AIRP=' . $previousAirpName . ', SCEN=Default, CRITERIA="CDPN=' . $previousAirpCDNP . '", OUTCOME="AIRP=' . $airpName . '";' . "\n";
                    $airpScenScripts[] =
                        'UPD-AIRP: AIRP=' . $previousAirpName . ';'. "\n";
                }

                if ($previousSubRouteTable) {
                    $airpScenScripts = array_merge($airpScenScripts, $previousSubRouteTable['script']);
                    if ($previousSubRouteTable['lastAirp']) {
                        $airpScenScripts[] =
                            'ADD-AIRP-SCEN: AIRP=' . $previousSubRouteTable['lastAirp'] . ', SCEN=Default, CRITERIA="CDPN=' . $previousSubRouteTable['lastAirp'] . '", OUTCOME="AIRP=' . $airpName . '";' . "\n";
                        $airpScenScripts[] =
                            'UPD-AIRP: AIRP=' . $previousSubRouteTable['lastAirp'] . ';'. "\n";
                    }
                    $previousSubRouteTable = false;
                }

                $airpScenScripts[] = "\n#### ADD SCENARIOS {$airpBaseScenName} ####\n";

                foreach ($prefixGroup as $prefix) {
                    if (strlen($prefix) === 1) {
                        $previousAirpArray[(int)$prefix] = true;
                    }
                    $criteria = 'CDPN=' . $prefix;
                    $criteria .= $cpcName ? ',CPC=' . $cpcName : '';
                    $scen = str_replace(']', '', str_replace('[', '_', $prefix));
                    $airpScenScripts[] =
                        'ADD-AIRP-SCEN: AIRP=' . $airpName . ', SCEN=' . $scen . ', CRITERIA="' . $criteria . '", OUTCOME="' . $outcomeScript . '";'. "\n";
                }

                $previousAirpName = $airpName;
                $previousAirpCDNP = '';
                for($i = 1; $i<=9; $i++) {
                    if (!isset($previousAirpArray[$i])) {
                        $previousAirpCDNP .= $i;
                    }
                }
                $previousAirpCDNP = $previousAirpCDNP == '123456789' ? '.' : '[' . $previousAirpCDNP . ']';
                $previousAirpArray = [];
            }

            if ($route->outcome_route_table_id) {
                $previousSubRouteTable = $subRouteTable;
            }
        }

        return [
            'firstAirp' => $firstAirp,
            'lastAirp' => $previousAirpName,
            'lastCDPN' => $previousAirpCDNP,
            'script' => $airpScenScripts,
        ];
    }

    private function getRoutesNumber($numberId, $airpBaseName, $outcomeScript)
    {
        $firstAirp = '';
        $previousAirpName = '';
        $previousAirpCDNP = '.';
        $previousAirpArray = [];
        $airpScenScripts = [];

        $number = Number::findOne($numberId);

        $cpcName = '';
        $airpBaseName = $this->spawnAirpKey($airpBaseName, $cpcName);
        $this->exportAirpLabel($airpBaseName);

        $prefixGroups = $this->getNumberPrefixGroups($number->id);
        $nGroup = 0;
        foreach ($prefixGroups as $key => $prefixGroup) {
            $nGroup++;
            $airpBaseScenName = $airpBaseName . (count($prefixGroups) > 1 && $nGroup > 1 ? '_' . $key : '');
            $airpName = $airpBaseScenName;

            if (!$firstAirp) {
                $firstAirp = $airpName;
            }

            $this->exportAirpByName($airpName);

            if ($previousAirpName) {
                $airpScenScripts[] =
                    'ADD-AIRP-SCEN: AIRP=' . $previousAirpName . ', SCEN=Default, CRITERIA="CDPN=' . $previousAirpCDNP . '", OUTCOME="AIRP=' . $airpName . '";' . "\n";
                $airpScenScripts[] =
                    'UPD-AIRP: AIRP=' . $previousAirpName . ';'. "\n";
            }

            $airpScenScripts[] = "\n#### ADD SCENARIOS {$airpBaseScenName} ####\n";

            foreach ($prefixGroup as $prefix) {
                if (strlen($prefix) === 1) {
                    $previousAirpArray[(int)$prefix] = true;
                }
                if ($number->type_id == Number::STATUS_A_NUMBER) {
                    $criteria = 'CGPN=' . $prefix;
                } else {
                    $criteria = 'CDPN=' . $prefix;
                }
                $criteria .= $cpcName ? ',CPC=' . $cpcName : '';
                $scen = str_replace(']', '', str_replace('[', '_', $prefix));
                $airpScenScripts[] =
                    'ADD-AIRP-SCEN: AIRP=' . $airpName . ', SCEN=' . $scen . ', CRITERIA="' . $criteria . '", OUTCOME="' . $outcomeScript . '";'. "\n";
            }

            $previousAirpName = $airpName;
            $previousAirpCDNP = '';
            for($i = 1; $i<=9; $i++) {
                if (!isset($previousAirpArray[$i])) {
                    $previousAirpCDNP .= $i;
                }
            }
            $previousAirpCDNP = $previousAirpCDNP == '123456789' ? '.' : '[' . $previousAirpCDNP . ']';
            $previousAirpArray = [];
        }

        return [
            'firstAirp' => $firstAirp,
            'lastAirp' => $previousAirpName,
            'lastCDPN' => $previousAirpCDNP,
            'script' => $airpScenScripts,
        ];
    }


    private function getRoutesAuto($pathRoute)
    {
        if ($this->autoAirpName) {
            return [
               'firstAirp' => $this->autoAirpName,
               'lastAirp' => '',
               'script' => [],
            ];
        }

        $previousAirpName = '';
        $previousAirpCDNP = '.';
        $previousAirpArray = [];
        $airpScenScripts = [];

        $airpBaseName = $this->spawnAirpKey('auto');
        $this->exportAirpLabel($airpBaseName);

        $prefixGroups = $this->getAutoRoutePrefixes();
        foreach ($prefixGroups as $key => $prefixGroup) {
            $airpBaseScenName = 'auto' . (count($prefixGroups) > 1 ? '_' . $key : '');
            $airpName = $pathRoute . $this->server->id . '_' . $airpBaseScenName;

            if (!$this->autoAirpName) {
                $this->autoAirpName = $airpName;
            }

            $this->exportAirpByName($airpName);

            if ($previousAirpName) {
                $airpScenScripts[] =
                    'ADD-AIRP-SCEN: AIRP=' . $previousAirpName . ', SCEN=Default, CRITERIA="CDPN=' . $previousAirpCDNP . '", OUTCOME="AIRP=' . $airpName . '";' . "\n";
                $airpScenScripts[] =
                    'UPD-AIRP: AIRP=' . $previousAirpName . ';'. "\n";
            }

            $airpScenScripts[] = "\n#### ADD SCENARIOS {$airpBaseScenName} ####\n";

            foreach ($prefixGroup as $prefix) {
                if (strlen($prefix['prefix']) === 1) {
                    $previousAirpArray[(int)$prefix['prefix']] = true;
                }
                $criteria = 'CDPN=' . $prefix['prefix'];
                $scen = str_replace(']', '', str_replace('[', '_', $prefix['prefix']));
                $airpScenScripts[] =
                    'ADD-AIRP-SCEN: AIRP=' . $airpName . ', SCEN=' . $scen . ', CRITERIA="' . $criteria . '", OUTCOME="RTCASE=' . $prefix['rtcase'] . '";'. "\n";
            }

            $previousAirpName = $airpName;
            $previousAirpCDNP = '';
            for($i = 1; $i<=9; $i++) {
                if (!isset($previousAirpArray[$i])) {
                    $previousAirpCDNP .= $i;
                }
            }
            $previousAirpCDNP = $previousAirpCDNP == '123456789' ? '.' : '[' . $previousAirpCDNP . ']';
            $previousAirpArray = [];
        }

        if ($previousAirpName) {
            $airpScenScripts[] = "UPD-AIRP: AIRP={$previousAirpName};\n";
        }

        return [
            'firstAirp' => $this->autoAirpName,
            'lastAirp' => '',
            'lastCDPN' => '',
            'script' => $airpScenScripts,
        ];
    }

    private function getNumberPrefixGroups($numberId)
    {
        $prefixes = [];
        $prefixlistIds = Number::findOne($numberId)->getPrefixLists();
        foreach ($prefixlistIds as $prefixlistId) {
            $prefixList = PrefixlistPrefix::find()->where(['prefixlist_id' => $prefixlistId])->select('prefix')->asArray()->all();
            foreach ($prefixList as $prefix) {
                $prefixes[] = $prefix['prefix'];
            }
        }

        sort($prefixes, SORT_STRING);
        $prefixes = array_unique($prefixes);
        $prefixes = self::reducePrefixes($prefixes);
        return $this->splitPrefixes($prefixes);
    }

    private function getAutoRoutePrefixes()
    {
        $operatorIdToCode = [];

        foreach($this->getAutoRoutingTrunks() as $operator) {
            $operatorIdToCode[$operator->id] = $operator->code;
        }

        $command =
            \Yii::$app->db
                ->createCommand("select prefix, routes from auth.select_routing_report(:serverId)", [':serverId' => $this->server->id]);
        $result = $command->queryAll();
        $groups = [];
        foreach ($result as $row) {
            $routes = [];
            if ($row['routes'] && $row['routes'] != '{}') {
                $n = 1;
                foreach(str_getcsv( trim($row['routes'], '{}') ) as $value) {
                    if ($n > 3) break;
                    $routes[] = str_pad($operatorIdToCode[$value], 2, '0', STR_PAD_LEFT);
                    $n++;
                }
            }
            $rtcase = 'rc_auto_' . implode('_', $routes);
            if (!isset($groups[$rtcase])) {
                $groups[$rtcase] = [];
            }
            $groups[$rtcase][] = $row['prefix'];
        }

        if (empty($groups)) {
            $groups[''] = [];
        }

        $hash = [];
        $allPrefixes = [];
        foreach ($groups as $rtcase => $prefixes) {
            sort($prefixes, SORT_STRING);
            $prefixes = array_unique($prefixes);
            $prefixes = self::reducePrefixesPart1($prefixes);
            $tmpPrefixes = self::reducePrefixesPart2($prefixes);

            $prefixes = [];
            foreach ($tmpPrefixes as $prefix) {
                if (!isset($hash[$prefix])) {
                    $hash[$prefix] = true;
                    $prefixes[] = $prefix;
                }
            }

            $prefixes = self::reducePrefixesPart3($prefixes);

            foreach ($prefixes as $prefix) {
                $allPrefixes[] = [
                    'prefix' => $prefix,
                    'rtcase' => $rtcase,
                ];
            }

        }
        $allPrefixes = $this->splitPrefixes($allPrefixes, 'prefix');
        return $allPrefixes;
    }

    /**
     * @return \app\models\Trunk[]
     */
    private function getAutoRoutingTrunks()
    {
        return
            Trunk::find()
                ->where(['server_id' => $this->server->id, 'auto_routing' => true])
                ->all()
            ;
    }

    private function exportAutoRouteCases()
    {
        $trunks = $this->getAutoRoutingTrunks();

        $routes = [];
        foreach ($trunks as $trunk) {
            $routes[] = [$trunk];
        }

        $tmpRoutes = $routes;
        $routes = [];
        foreach ($tmpRoutes as $tmpTrunks) {
            foreach ($trunks as $trunk) {
                $routes[] = array_merge($tmpTrunks, [$trunk]);
            }
            foreach ($trunks as $trunk) {
                $routes[] = [$trunk];
            }
        }

        $tmpRoutes = $routes;
        $routes = [];
        foreach ($tmpRoutes as $tmpTrunks) {
            foreach ($trunks as $trunk) {
                $routes[] = array_merge($tmpTrunks, [$trunk]);
            }
            foreach ($trunks as $trunk) {
                $routes[] = [$trunk];
            }
        }

        $routesMap = [];
        foreach ($routes as $tmpTrunks) {
            $rtcase = 'rc_auto';
            $tmpTrunks = array_unique($tmpTrunks, SORT_REGULAR);
            foreach ($tmpTrunks as $trunk) {
                $rtcase .= '_' . str_pad($trunk->id, 2, '0', STR_PAD_LEFT);
            }

            if (!isset($routesMap[$rtcase])) {
                $routesMap[$rtcase] = $tmpTrunks;
            }
        }

        ksort($routesMap);

        foreach ($routesMap as $rtcase => $tmpTrunks) {
            $strOperators = '';
            $n = 1;
            foreach ($tmpTrunks as $trunk) {
                $strOperators .= 'RO-' . $n . '="' . $trunk->trunk_name . ',' . $n . ',100;", ';
                $n++;
            }

            $this->routeCaseScripts[] = [
                'script' => "ADD-RTCASE: {$strOperators}RTCASE={$rtcase};\n",
            ];
        }
    }

    private function exportRouteCase($routeCaseId)
    {
        if (!isset($this->routeCaseScripts[$routeCaseId])) {
            $routeCase = RouteCase::findOne($routeCaseId);
            $rcOperators = RouteCaseTrunk::find()->where(['route_case_id' => $routeCase->id])->all();
            $strOperators = '';
            $n = 1;
            foreach ($rcOperators as $rcOperator) {
                $trunk = Trunk::findOne($rcOperator->trunk_id);
                $strOperators .= 'RO-' . $n . '="' . $trunk->trunk_name . ',' . $rcOperator->priority . ',' . $rcOperator->weight . ';", ';
                $n++;
            }

            $name = strtolower($routeCase->name);
            $outcome = "RTCASE={$name}";
            $script = "ADD-RTCASE: {$strOperators}RTCASE={$name};\n";

            $this->routeCaseScripts[$routeCaseId] = [
                'script' => $script,
                'outcome' => $outcome,
            ];
            return $outcome;
        } else {
            return $this->routeCaseScripts[$routeCaseId]['outcome'];
        }
    }

    private function exportAirp($airpId)
    {
        if (!isset($this->airpScripts[$airpId])) {
            $airp = Airp::findOne($airpId);

            $name = $this->spawnAirpKey($airp->name);
            $outcome = "AIRP={$name}";

            $script = "\n#### ADD AIRP {$name} ####\n";
            $script .= "ADD-AIRP: DEFAULT-REASON=UNALLOCATED_UNASSIGNED_NUMBER, AIRP={$name};\n";
            $script .= "UPD-AIRP: AIRP={$name};\n";

            $this->airpScripts[$airpId] = [
                'script' => $script,
                'outcome' => $outcome,
            ];
            return $outcome;
        } else {
            return $this->airpScripts[$airpId]['outcome'];
        }
    }

    private function exportAirpLabel($label)
    {
        $this->airpScripts[] = [
            'script' => "\n#### ADD AIRP {$label} ####\n",
        ];
    }

    private function exportAirpByName($name)
    {
        $script = "ADD-AIRP: DEFAULT-REASON=UNALLOCATED_UNASSIGNED_NUMBER, AIRP={$name};\n";
        $script .= "UPD-AIRP: AIRP={$name};\n";

        $this->airpScripts[] = [
            'script' => $script,
        ];
    }

    private function exportReleaseReason($releaseReasonId)
    {
        if (!isset($this->releaseReasonScripts[$releaseReasonId])) {
            $releaseReason = ReleaseReason::findOne($releaseReasonId);

            $name = strtolower($releaseReason->name);
            $outcome = "RELEASE_REASON={$name}";

            $this->releaseReasonScripts[$releaseReasonId] = [
                'outcome' => $outcome,
            ];
            return $outcome;
        } else {
            return $this->releaseReasonScripts[$releaseReasonId]['outcome'];
        }
    }

    private function spawnAirpKey($name, $cpcName = '')
    {
        $airpKey = strtolower($name . ($cpcName ? '_' . $cpcName : ''));
        if (isset($this->airpKeys[$airpKey])) {
            $airpKey .= '_r' . rand(10000, 99999);
        }
        $this->airpKeys[$airpKey] = true;
        return $airpKey;
    }


    private static function reducePrefixes(array $prefixes)
    {
        $prefixes = self::reducePrefixesPart1($prefixes);

        $prefixes = self::reducePrefixesPart2($prefixes);

        $prefixes = self::reducePrefixesPart3($prefixes);

        return $prefixes;
    }

    private static function reducePrefixesPart1(array $table)
    {
        $newTable = [];
        $pre_prefix = '';

        $pre_l = 0;
        $m_prefix = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];

        foreach ($table as $prefix) {
            $cur_l = strlen($prefix);
            if ($pre_l <> $cur_l || substr($prefix, 0, $cur_l - 1) <> substr($prefix, 0, $pre_l - 1)) {
                if ($pre_l > $cur_l) $n = $pre_l; else $n = $cur_l;
                while ($n > 0) {
                    if ($m_prefix[$n] == '' || $m_prefix[$n] <> substr($prefix, 0, strlen($m_prefix[$n]))) {
                        $m_prefix[$n] = '';
                    }
                    $n = $n - 1;
                }
                $pre_prefix = '';
                $n = $cur_l - 1;
                while ($n > 0) {
                    if ($pre_prefix === '' && $m_prefix[$n] !== '')
                        $pre_prefix = $m_prefix[$n];
                    $n = $n - 1;
                }
            }
            $m_prefix[$cur_l] = $prefix;

            if ($pre_prefix != '' && strpos($prefix, $pre_prefix) === 0) {
                continue;
            }

            $newTable[] = $prefix;
        }
        return $newTable;
    }

    private static function reducePrefixesPart2(array $table)
    {
        while (true) {
            $needReduce = false;
            $newTable = [];
            $m = [];
            $pre_len = 0;
            $pre_subprefix = '';

            foreach ($table as $prefix) {
                $len = strlen($prefix);
                $subprefix = substr($prefix, 0, $len - 1);

                if ($len != $pre_len || $subprefix != $pre_subprefix) {
                    if (count($m) < 10) {
                        foreach ($m as $mm) {
                            $newTable[] = $mm;
                        }
                    } else {
                        $newTable[] = substr($m[0], 0, strlen($m[0]) - 1);
                        $needReduce = true;
                    }

                    $m = array($prefix);
                } else {
                    $m[] = $prefix;
                }
                $pre_len = $len;
                $pre_subprefix = $subprefix;
            }
            if (count($m) < 10) {
                foreach ($m as $mm) {
                    $newTable[] = $mm;
                }
            } else {
                $newTable[] = substr($m[0], 0, strlen($m[0]) - 1);
                $needReduce = true;
            }

            if (!$needReduce) {
                return $newTable;
            }
            $table = $newTable;
        }

    }

    private static function reducePrefixesPart3(array $table)
    {
        $newTable = [];
        $m = [];
        $pre_len = 0;
        $pre_subprefix = '';

        foreach ($table as $prefix) {
            $len = strlen($prefix);
            $subprefix = substr($prefix, 0, $len - 1);

            if ($len != $pre_len || $subprefix != $pre_subprefix) {
                if (count($m) > 1) {
                    $str = '';
                    foreach ($m as $mm) {
                        $str .= substr($mm, strlen($mm) - 1);
                    }
                    $newTable[] = substr($m[0], 0, strlen($m[0]) - 1) . '[' . $str . ']';
                } elseif (count($m) > 0) {
                    $newTable[] = $m[0];
                }
                $m = array($prefix);
            } else {
                $m[] = $prefix;
            }
            $pre_len = $len;
            $pre_subprefix = $subprefix;
        }
        if (count($m) > 1) {
            $str = '';
            foreach ($m as $mm) {
                $str .= substr($mm, strlen($mm) - 1);
            }
            $newTable[] = substr($m[0], 0, strlen($m[0]) - 1) . '[' . $str . ']';
        } elseif (count($m) > 0) {
            $newTable[] = $m[0];
        }

        return $newTable;
    }

    private function splitPrefixes(array $prefixes, $field = null)
    {
        $chunks = [
            '' => $prefixes
        ];

        $minLen = 1;

        while (true) {
            $needSplit = false;

            foreach ($chunks as $key => $chunk) {
                if (count($chunk) > 3000) {

                    $len = strlen($key) + 1;
                    $len = $len < $minLen ? $minLen : $len;

                    unset($chunks[$key]);

                    foreach ($chunk as $item) {
                        $prefix = $field ? $item[$field] : $item;
                        if ($pos = strpos($prefix, '[')) {
                            $prefix = substr($prefix, 0, $pos);
                        }
                        $subprefix = substr($prefix, 0, $len);
                        if (!isset($chunks[$subprefix])) {
                            $chunks[$subprefix] = [];
                        }
                        $chunks[$subprefix][] = $item;
                    }

                    $needSplit = true;
                }
            }
            if (!$needSplit) {
                break;
            }
        }

        ksort($chunks, SORT_STRING);

        return $chunks;
    }
}