var StatisticsTreeCtrl = function($scope, StatisticsTree, $window) {

    $scope.path = '';
    $scope.pathName = '';

    $scope.refresh = function() {
        StatisticsTree.get({server_id: $scope.server.id, path: $scope.path, path_name: $scope.pathName}).then(function (data) {
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
        $scope.pathName = subitem.path_name;
        $scope.refresh();
    };

    $scope.ascend = function() {
        var path = "" + $scope.path;
        var pathArray = path.split(',');

        pathArray.pop();

        var pathName = "" + $scope.pathName;
        var pathNameArray = pathName.split('/');

        pathNameArray.pop();

        $scope.path = pathArray.join();
        $scope.pathName = pathNameArray.join('/');

        $scope.refresh();
    };
};