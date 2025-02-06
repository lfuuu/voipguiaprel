var TestCallShowTestCtrl = function($scope, TestCall, params, $modalInstance, $window) {

    $scope.details = 1;

    if (params.id) {
        TestCall.result({id: params.id, displayTreeView: true}).then(function(data){
            $scope.item = data.item;
            $scope.isStageRowType = function (row) {
                return row.type == 'STAGE';
            };
            $scope.result = data.result;
            $scope.result_new = data.result_new;
            $scope.key = data.key;
            $scope.url = data.url + '&server_id=' + $scope.server.id;
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id
        };
    }

    $scope.descend = function (item) {
        if (item.steps && item.steps.length == 0) {
            TestCall.descend({'path': item.path, 'key': $scope.key}).then(function (result) {
                var pathArray = item.path.split(',');

                $scope.updateItemRecursively($scope.result_new, pathArray, result.steps);
            });
        }
    };

    $scope.updateItemRecursively = function (item, path, steps) {
        if (path.length > 0) {
            var index = path.shift();
            $scope.updateItemRecursively(item['steps'][index], path, steps);
        } else {
            item.steps = steps;
        }
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