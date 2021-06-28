var PricelistFilterBHistoryViewCtrl = function($rootScope, $scope, PricelistFilterBHistoryItem, params, $modalInstance, Redirect) {
    if (params.id) {
        PricelistFilterBHistoryItem.read({pricelist_filter_b_history_id: params.id}).then(function (data) {
            $scope.list = data;
            console.log(data);
        });
    } else {
        $scope.list = [];
    }

    $scope.editFilterB = function (id) {
        console.log(id);
        Redirect.pricelistFilterBEdit(id);
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
