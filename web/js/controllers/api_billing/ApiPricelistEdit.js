var ApiBillingApiPricelistEditCtrl = function($rootScope, $scope, ApiBillingApiPricelist, params, $modalInstance) {
    if (params.id) {
        ApiBillingApiPricelist.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            name: ''
        };
    }

    $scope.save = function () {
        ApiBillingApiPricelist.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
