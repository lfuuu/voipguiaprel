var MoneyTreeCtrl = function($scope, MoneyTree, $window) {

    $scope.path = '';
    $scope.pathName = '';
    $scope.coreKey = '';

    $scope.refresh = function() {
        MoneyTree.get({server_id: $scope.server.id, path: $scope.path, core_key: $scope.coreKey}).then(function (data) {
            $scope.result = {};
            $scope.result.sub = [data.result];
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

    $scope.descend = function (subitem, collapsed) {
        var coreKey = $scope.coreKey;

        if (!collapsed) {
            MoneyTree.get({server_id: $scope.server.id, path: subitem.path, core_key: coreKey}).then(function (result) {
                var pathArray = (coreKey + "," + subitem.path).split(',');
                var wantedResult = result.result.sub;

                $scope.updateItemRecursively($scope.result, pathArray, wantedResult);
            });
        }
    };

    $scope.updateItemRecursively = function (item, path, subitems) {
        if (path.length > 0) {
            var index = path.shift();

            if (!index) {
                index = '0';
            }

            var elementPos = item['sub'].map(function(x) {return x.key.toString();}).indexOf(index);

            $scope.updateItemRecursively(item['sub'][elementPos], path, subitems);
        } else {
            item.sub = subitems;
        }
    };
};