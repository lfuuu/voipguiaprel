var TestAuthShowTestPrimaryCtrl = function($scope, TestAuth, params, $modalInstance) {

    $scope.details = 0;

    if (params.id) {
        TestAuth.result({id: params.id}).then(function (data) {
            $scope.item = data.item;
            $scope.result = data.result;
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