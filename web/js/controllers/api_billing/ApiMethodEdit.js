var ApiBillingApiMethodEditCtrl = function($rootScope, $scope, ApiBillingApiMethod, params, $modalInstance) {
    if (params.id) {
        ApiBillingApiMethod.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            name: ''
        };
    }

    $scope.save = function () {
        ApiBillingApiMethod.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
