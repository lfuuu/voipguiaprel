var StatisticsTreeCtrl = function($scope, StatisticsTree, $window) {

    $scope.path = '';
    $scope.pathName = '';
    $scope.coreKey = '';

    $scope.refresh = function() {
        StatisticsTree.get({server_id: $scope.server.id, path: $scope.path, core_key: $scope.coreKey}).then(function (data) {
            $scope.result = data.result;
            $scope.coreKey = data.core_key;
        });
    };

    $scope.refresh();

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

    $scope.descend = function (subitem, collapsed) {
        if (subitem.subitems && subitem.subitems.length == 0 && !collapsed) {
            StatisticsTree.get({server_id: $scope.server.id, path: subitem.path, core_key: $scope.coreKey}).then(function (result) {
                var pathArray = ($scope.coreKey + "," + subitem.path).split(',');

                for (var i in result.result.subitems) {
                    var wantedResult = result.result.subitems[i].subitems;
                    break;
                }

                $scope.updateItemRecursively($scope.result, pathArray, wantedResult);
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