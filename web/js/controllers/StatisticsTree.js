var StatisticsTreeCtrl = function($scope, StatisticsTree, $window) {

    $scope.origPath = '';
    $scope.termPath = '';
    $scope.pathName = '';
    $scope.origCoreKey = '';
    $scope.termCoreKey = '';

    $scope.refresh = function() {
        StatisticsTree.get({server_id: $scope.server.id, path: $scope.origPath, core_key: $scope.origCoreKey, is_orig: true}).then(function (data) {
            $scope.origResult = data.result;
            $scope.origCoreKey = data.core_key;
        });

        StatisticsTree.get({server_id: $scope.server.id, path: $scope.termPath, core_key: $scope.termCoreKey, is_orig: false}).then(function (data) {
            $scope.termResult = data.result;
            $scope.termCoreKey = data.core_key;
        });
    };

    $scope.refresh();

    $scope.collapseAll = function () {
        $scope.$broadcast('angular-ui-tree:collapse-all');
    };

    $scope.expandAll = function () {
        $scope.$broadcast('angular-ui-tree:expand-all');
    };

    $scope.descend = function (subitem, collapsed, isOrig) {
        var coreKey;
        var resultName;

        if (isOrig) {
            coreKey = $scope.origCoreKey;
            resultName = 'origResult';
        } else {
            coreKey = $scope.termCoreKey;
            resultName = 'termResult';
        }

        if (subitem.subitems && subitem.subitems.length == 0 && !collapsed) {
            StatisticsTree.get({server_id: $scope.server.id, path: subitem.path, core_key: coreKey, is_orig: isOrig}).then(function (result) {
                var pathArray = (coreKey + "," + subitem.path).split(',');

                for (var i in result.result.subitems) {
                    var wantedResult = result.result.subitems[i].subitems;
                    break;
                }

                $scope.updateItemRecursively($scope[resultName], pathArray, wantedResult);
            });
        }
    };

    $scope.updateItemRecursively = function (item, path, subitems) {
        if (path.length > 0) {
            var index = path.shift();
            $scope.updateItemRecursively(item['subitems'][index], path, subitems);
        } else {
            item.subitems = subitems;
        }
    };
};