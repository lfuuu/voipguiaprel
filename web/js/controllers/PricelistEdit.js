var PricelistEditCtrl = function($scope, List, Pricelist, params, $modalInstance, $window) {

    if (params.id) {
        Pricelist.get({id: params.id}).then(function(data){
            $scope.item = data;
        });
    } else {
        $scope.item = {
            pricelist_version: 1
        };
    }

    $scope.currency = List.currency();

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