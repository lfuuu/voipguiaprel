var PricelistEditCtrl = function($scope, List, Pricelist, PricelistLocation, params, $modalInstance, $window) {

    if (params.id) {
        Pricelist.get({id: params.id}).then(function(data){
            $scope.item = data;

            PricelistLocation.listByPricelist({'pricelist_id': $scope.item.id}).then(function (data) {
                $scope.locations = data;
            });
        });
    } else {
        $scope.item = {
            pricelist_version: 1
        };
    }

    $scope.currency = List.currency();

    List.pricelistGroup().then(function (data) {
        $scope.pricelistGroupList = data;
    });

    $scope.save = function()
    {
        Pricelist.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    };
};