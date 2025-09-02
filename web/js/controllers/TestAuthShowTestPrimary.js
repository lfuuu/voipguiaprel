var TestAuthShowTestPrimaryCtrl = function($scope, TestAuth, Redirect, params, $modalInstance) {

    $scope.details = 1;
    $scope.type = 'Primary';

    // Храним только доменные INFO из RouteTableProcessor
    // В простейшем варианте — массив строк; если понадобится связь 1:1 с outcome_id, расширим структуру.
    $scope.msTeamsDomainInfos = []; // ["Найден домен MS Teams: mcntele.sbc.kompaas.tel для номера B", ...]

    // ===== Helpers: определяем узлы "RouteTableProcessor" =====
    $scope._isRouteTableNode = function(node) {
        return node && node.name && node.name.indexOf('Отрабатываем таблицу маршрутизации') === 0 && node.path;
    };

    // Собираем нужные узлы из дерева
    $scope._collectRouteTableNodes = function(root) {
        var out = [];
        (function dfs(n){
            if (!n) return;
            if ($scope._isRouteTableNode(n)) out.push(n);
            if (n.steps && n.steps.length) {
                for (var i=0;i<n.steps.length;i++) dfs(n.steps[i]);
            }
        })(root);
        return out;
    };

    // Из RouteTableProcessor-steps вытащить нужную строку "Найден домен MS Teams: ..."
    $scope._extractMsTeamsDomainInfo = function(steps) {
        if (!steps) return null;
        for (var i=0;i<steps.length;i++) {
            var s = steps[i];
            if (s && s.type === 'INFO' && typeof s.trace === 'string' &&
                s.trace.indexOf('Найден домен MS Teams:') === 0) {
                return s.trace; // возвращаем первую найденную
            }
        }
        return null;
    };

    // ===== Предзагрузка: вызвать descend ТОЛЬКО для RouteTableProcessor узлов и извлечь доменные INFO =====
    $scope._preloadRouteTableInfos = function(key, root) {
        var nodes = $scope._collectRouteTableNodes(root);
        if (!nodes || !nodes.length) return;

        nodes.forEach(function(node){
            if (node._rtLoaded) return;
            TestAuth.descend({ path: node.path, key: key }).then(function(result){
                node._rtLoaded = true;
                node.steps = result.steps || [];
                var info = $scope._extractMsTeamsDomainInfo(node.steps);
                if (info && $scope.msTeamsDomainInfos.indexOf(info) === -1) {
                    $scope.msTeamsDomainInfos.push(info);
                }
            });
        });
    };

    // ===== API для шаблона: получить текст домена под RESULT / ROUTE CASE =====
    $scope.getMsTeamsDomainInfoForResult = function(row) {
        // Показ под "ROUTE CASE ...". Если нашли несколько — берём первую (обычно она одна).
        if (!row || row.type !== 'RESULT') return null;
        if (!$scope.msTeamsDomainInfos.length) return null;

        // Если нужен жёсткий фильтр по имени кейса — можно проверять row.action/row.params.
        // Здесь выводим для любого RESULT с action "ROUTE CASE".
        if (row.action && row.action.indexOf('ROUTE CASE') === 0) {
            return $scope.msTeamsDomainInfos[0];
        }
        return null;
    };

    // ===== Инициализация =====
    if (params.id) {
        TestAuth.result({id: params.id, displayTreeView: true}).then(function (data) {
            $scope.item = data.item;
            $scope.isStageRowType = function (row) {
                return row.type == 'STAGE';
            };
            $scope.result_new = data.result_new;
            $scope.full_item = data;
            $scope.key = data.key;
            $scope.url = data.url;

            // ВАЖНО: грузим ТОЛЬКО RouteTableProcessor (никаких OutcomeProcessor)
            $scope._preloadRouteTableInfos($scope.key, $scope.result_new);
        });
    } else {
        $scope.item = { server_id: $scope.server.id };
    }

    // ===== Остальной существующий функционал (без изменений) =====
    $scope.descend = function (item) {
        if (item.steps && item.steps.length == 0) {
            TestAuth.descend({'path': item.path, 'key': $scope.key}).then(function (result) {
                var pathArray = item.path.split(',');
                $scope.updateItemRecursively($scope.result_new, pathArray, result.steps);
            });
        }
    };

    $scope.isObject = function (item) {
        return (typeof item === 'object');
    };

    $scope.updateItemRecursively = function (item, path, steps) {
        if (path.length > 0) {
            var index = path.shift();
            $scope.updateItemRecursively(item['steps'][index], path, steps);
        } else {
            item.steps = steps;
        }
    };

    $scope.createTest = function (item) {
        var params = {
            server_id: item.server_id,
            trunk_name: item.name,
            dst_number: $scope.item.dst_number,
            src_number: $scope.item.src_number,
            testgroup_id: $scope.item.testgroup_id,
            headers: item.headers
        };

        Redirect.testAuthCreateAndFill(params).then(function () {
            if ($scope.init) $scope.init();
        });
    };

    $scope.clickTrunk = function (item, full_item) {
        var params = {
            server_id: full_item.item.server_id,
            trunk_name: item.back_trunk,
            orig_trunk: item.name,
            trace_to_regions: item.trace_to_regions,
            src_number: full_item.item.src_number,
            dst_number: full_item.item.dst_number,
            src_noa: full_item.item.src_noa,
            dst_noa: full_item.item.dst_noa,
            redirect_number: full_item.item.redirect_number,
            router_version: full_item.item.router_version,
            with_debug_info: full_item.item.with_debug_info,
            ttl: full_item.item.ttl - 1,
            headers: full_item.item.headers,
        };

        TestAuth.trace(params).then(function (result) {
            full_item.trace[result.key] = result;
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };

    $scope.collapseAll = function () {
        $scope.$broadcast('angular-ui-tree:collapse-all');
    };

    $scope.expandAll = function () {
        $scope.$broadcast('angular-ui-tree:expand-all');
    };

    $scope.$on('angular-ui-tree:collapse-all', function () {
        $scope.collapsed = true;
    });

    $scope.$on('angular-ui-tree:expand-all', function () {
        $scope.collapsed = false;
    });
};
