var ApiBillingApiEditCtrl = function($rootScope, $scope, ApiBillingApi, params, $modalInstance) {
    if (params.id) {
        ApiBillingApi.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id,
            name: ''
        };
    }

    $scope.save = function () {
        ApiBillingApi.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
