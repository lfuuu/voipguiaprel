var PricelistSinglePrefixHistoryViewCtrl = function($rootScope, $scope, PricelistPrefixPrice, params, $modalInstance) {
    if (params.filter_b_id) {
        PricelistPrefixPrice.singleHistory(params.filter_b_id, params.prefix_b).then(function (data) {
            $scope.list = data;
        });
    } else {
        $scope.list = [];
    }
    
    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
