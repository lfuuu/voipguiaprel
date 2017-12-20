var TestAuthShowTestReserve2Ctrl = function($scope, TestAuth, params, $modalInstance) {

    $scope.details = 0;

    if (params.id) {
        TestAuth.result({id: params.id, isReserve2: true, displayTreeView: params.displayTreeView}).then(function (data) {
            $scope.item = data.item;
            $scope.result = data.result;
            $scope.isStageRowType = function (row) {
                return row.type == 'STAGE';
            };
            $scope.result_new = data.result_new;
            if ($scope.result_new == null) {
                $scope.details = 4;
            }
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id
        };
    }

    $scope.save = function () {
        TestAuth.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    }

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