var ApiBillingApiPricelistItemEditCtrl = function($rootScope, $scope, ApiBillingApiPricelistItem, params, $modalInstance) {
    $scope.currencyOptions = ['RUB', 'EUR', 'HUF', 'USD'];

    if (params.id) {
        ApiBillingApiPricelistItem.get({id: params.id}).then(function (data) {
            if (!data.price_currency_id) data.price_currency_id = 'RUB';
            if (!data.cost_currency_id) data.cost_currency_id = 'RUB';
            $scope.item = data;
        });
    } else {
        $scope.item = {
            api_id: '',
            api_method_id: '',
            pricelist_id: '',
            price: 0,
            cost: 0,
            price_currency_id: 'RUB',
            cost_currency_id: 'RUB',
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
