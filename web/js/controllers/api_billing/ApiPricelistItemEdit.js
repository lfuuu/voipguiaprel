var ApiBillingApiPricelistItemEditCtrl = function($rootScope, $scope, ApiBillingApiPricelistItem, params, $modalInstance) {
    if (params.id) {
        ApiBillingApiPricelistItem.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            name: ''
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
