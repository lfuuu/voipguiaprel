var StatisticsTreeCtrl = function($scope, StatisticsTree, $window) {

    $scope.path = '';

    $scope.refresh = function() {
        StatisticsTree.get({server_id: $scope.server.id, path: $scope.path}).then(function (data) {
            $scope.result = data;
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

    $scope.descend = function(subitem) {
        $scope.path = subitem.path;
        $scope.refresh();
    }

    $scope.ascend = function() {
        var path = "" + $scope.path;
        var pathArray = path.split(',');

        pathArray.pop();

        $scope.path = pathArray.join();

        $scope.refresh();
    }
};