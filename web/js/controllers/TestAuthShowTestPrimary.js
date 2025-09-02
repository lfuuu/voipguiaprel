var TestAuthShowTestPrimaryCtrl = function($scope, TestAuth, Redirect, params, $modalInstance) {

    $scope.details = 1;
    $scope.type = 'Primary';

    // Кэши предзагруженных данных
    $scope.outcomeHeaders = {};             // { outcomeName: [steps] }
    $scope.routeTableInfoByOutcomeId = {};  // { outcomeId: "Найден домен MS Teams: ..." }

    // ===== Helpers: определение типов узлов дерева =====
    $scope._isOutcomeNode = function(node) {
        return node && node.name && node.name.indexOf('Выполнение outcome-действия') === 0 && node.path;
    };

    $scope._isRouteTableNode = function(node) {
        return node && node.name && node.name.indexOf('Отрабатываем таблицу маршрутизации') === 0 && node.path;
    };

    // Собрать интересующие узлы разом
    $scope._collectSpecialNodes = function(root) {
        var out = { outcome: [], routeTables: [] };
        (function dfs(n){
            if (!n) return;
            if ($scope._isOutcomeNode(n)) out.outcome.push(n);
            if ($scope._isRouteTableNode(n)) out.routeTables.push(n);
            if (n.steps && n.steps.length) {
                for (var i=0;i<n.steps.length;i++) dfs(n.steps[i]);
            }
        })(root);
        return out;
    };

    // ===== Парсеры содержимого шагов =====
    // Из OUTCOME-steps: INFO|OUTCOME|EU_MS_Teams (1376) -> имя
    $scope._extractOutcomeNameFromSteps = function(steps) {
        if (!steps || !steps.length) return null;
        for (var i = 0; i < steps.length; i++) {
            var s = steps[i];
            if (s.type === 'INFO' && typeof s.trace === 'string') {
                var m = s.trace.match(/^INFO\|OUTCOME\|(.+?)\s*\(\d+\)\s*$/);
                if (m) return m[1].trim();
            }
        }
        return null;
    };

    // Из RouteTableProcessor-steps: последняя INFO "Выходим по строке [...] outcome_id=1376" -> 1376
    $scope._extractOutcomeIdFromRouteTableSteps = function(steps) {
        if (!steps) return null;
        // идём с конца — ближе к "Выходим по строке ..."
        for (var i = steps.length - 1; i >= 0; i--) {
            var s = steps[i];
            if (s && s.trace && typeof s.trace === 'string') {
                var m = s.trace.match(/outcome_id\s*=\s*(\d+)/i);
                if (m) return m[1];
            }
        }
        return null;
    };

    // Из RouteTableProcessor-steps вытащить нужную строку "Найден домен MS Teams: ..."
    $scope._extractDomainInfo = function(steps) {
        if (!steps) return null;
        for (var i=0;i<steps.length;i++) {
            var s = steps[i];
            if (s.type === 'INFO' && typeof s.trace === 'string' &&
                s.trace.indexOf('Найден домен MS Teams:') === 0) {
                return s.trace;
            }
        }
        return null;
    };

    // ===== Предзагрузка: после получения дерева, вызвать descend у нужных узлов =====
    $scope._preloadSpecial = function(key, root) {
        var nodes = $scope._collectSpecialNodes(root);

        // OUTCOME узлы — сохраняем steps по имени (опционально)
        nodes.outcome.forEach(function(node){
            if (node._headersLoaded) return;
            TestAuth.descend({ path: node.path, key: key }).then(function(result){
                node._headersLoaded = true;
                node.steps = result.steps || [];
                var name = $scope._extractOutcomeNameFromSteps(node.steps);
                if (name) $scope.outcomeHeaders[name] = node.steps;
            });
        });

        // ROUTE TABLE узлы — достаём outcome_id и строку "Найден домен MS Teams: ..."
        nodes.routeTables.forEach(function(node){
            if (node._rtLoaded) return;
            TestAuth.descend({ path: node.path, key: key }).then(function(result){
                node._rtLoaded = true;
                node.steps = result.steps || [];
                var outcomeId = $scope._extractOutcomeIdFromRouteTableSteps(node.steps);
                var domainInfo = $scope._extractDomainInfo(node.steps);
                if (outcomeId && domainInfo) {
                    $scope.routeTableInfoByOutcomeId[outcomeId] = domainInfo;
                }
            });
        });
    };

    // ===== API получения данных для шаблона =====
    // steps «хедера» OUTCOME по строке RESULT (опционально)
    $scope.getOutcomeHeaderSteps = function(row) {
        try {
            if (!row || !row.params || !row.params.length) return null;
            var outcomeName = row.params[0].name; // напр., EU_MS_Teams
            return $scope.outcomeHeaders[outcomeName] || null;
        } catch(e) { return null; }
    };

    // outcomeId из шагов OUTCOME по строке RESULT
    $scope.getOutcomeIdFromResult = function(row) {
        var steps = $scope.getOutcomeHeaderSteps(row);
        if (!steps) return null;
        for (var i=0;i<steps.length;i++){
            var s = steps[i];
            if (s.type === 'INFO' && typeof s.trace === 'string') {
                var m = s.trace.match(/^INFO\|OUTCOME\|.+?\s*\((\d+)\)\s*$/);
                if (m) return m[1];
            }
        }
        return null;
    };

    // Конкретная строка "Найден домен MS Teams: ..." по строке RESULT
    $scope.getRouteTableDomainInfo = function(row) {
        var outcomeId = $scope.getOutcomeIdFromResult(row);
        if (outcomeId && $scope.routeTableInfoByOutcomeId[outcomeId]) {
            return $scope.routeTableInfoByOutcomeId[outcomeId];
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

            // Предзагрузка специальных узлов (OUTCOME + ROUTE TABLE)
            $scope._preloadSpecial($scope.key, $scope.result_new);
        });
    } else {
        $scope.item = { server_id: $scope.server.id };
    }

    // ===== Остальной существующий функционал =====
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
            $scope.init && $scope.init();
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
