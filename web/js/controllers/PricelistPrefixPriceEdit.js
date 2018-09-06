var PricelistPrefixPriceEditCtrl = function($scope, List, PricelistPrefixPrice, params, $modalInstance, $window) {

    if (params.id) {
        PricelistPrefixPrice.get({id: params.id}).then(function(data){
            $scope.item = data;
        });
    } else if (params.filter_b_id) {
        $scope.item = {
            pricelist_filter_b_id: params.filter_b_id
        };
    }else {
        $scope.item = {};
    }

    $scope.save = function()
    {
        PricelistPrefixPrice.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    };
};