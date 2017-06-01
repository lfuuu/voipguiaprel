var TestAuthShowTestReserveCtrl = function($scope, TestAuth, params, $modalInstance) {

    $scope.details = 0;

    if (params.id) {
        TestAuth.result({id: params.id, isReserve: true}).then(function (data) {
            $scope.item = data.item;
            $scope.result = data.result;
            $scope.isStageRowType = function (row) {
                return row.type == 'STAGE';
            };
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
};