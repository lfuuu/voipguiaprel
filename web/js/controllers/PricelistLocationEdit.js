var PricelistLocationEditCtrl = function($scope, List, PricelistLocation, params, $modalInstance, $window) {

    if (params.id) {
        PricelistLocation.get({id: params.id}).then(function(data){
            $scope.item = data;
        });
    } else if (params.pricelist_id) {
        $scope.item = {
            pricelist_id: params.pricelist_id
        };
    } else {
        $scope.item = {};
    }

    $scope.location = List.location();

    $scope.save = function()
    {
        PricelistLocation.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    };
};