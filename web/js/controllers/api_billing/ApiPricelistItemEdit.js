var ApiBillingApiPricelistItemEditCtrl = function($rootScope, $scope, ApiBillingApiPricelistItem, params, $modalInstance) {
    if (params.id) {
        ApiBillingApiPricelistItem.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            api_id: '',
            api_method_id: '',
            pricelist_id: '',
            price: 0,
            cost: 0,
            enabled: true
        };
    }

    $scope.save = function () {
        ApiBillingApiPricelistItem.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
