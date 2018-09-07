var PricelistLocationEditCtrl = function($scope, List, PricelistLocation, params, $modalInstance, $window) {

    if (params.id) {
        PricelistLocation.get({id: params.id}).then(function(data){
            $scope.item = data;
            $scope.item.mcc = $scope.item.mcc.replace('{', '').replace('}', '');
            $scope.item.mnc = $scope.item.mnc.replace('{', '').replace('}', '');
        });
    } else if (params.pricelist_id) {
        $scope.item = {
            pricelist_id: params.pricelist_id,
            location_id: 1
        };
    } else {
        $scope.item = {
            location_id: 1
        };
    }

    $scope.location = List.location();

    $scope.save = function()
    {
        var data = angular.copy($scope.item);

        data.mcc = '{' + data.mcc + '}';
        data.mnc = '{' + data.mnc + '}';

        PricelistLocation.save(data).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    };
};