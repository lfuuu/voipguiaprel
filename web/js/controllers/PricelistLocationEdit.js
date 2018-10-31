var PricelistLocationEditCtrl = function($scope, List, PricelistLocation, Mcc, Mnc, params, $modalInstance, $window) {

    $scope.pricelistIsActive = params.pricelist_is_active;

    if (params.id) {
        PricelistLocation.get({id: params.id}).then(function(data){
            $scope.item = data;
            $scope.item.mcc = $scope.item.mcc.replace('{', '').replace('}', '').split(',');
            $scope.item.mnc = $scope.item.mnc.replace('{', '').replace('}', '').split(',');
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

    Mcc.list().then(function (result) {
        $scope.mccList = result;
    });

    Mnc.list().then(function (result) {
        $scope.mncList = result;
    });

    $scope.location = List.location();

    $scope.save = function()
    {
        var data = angular.copy($scope.item);

        data.mcc = (typeof data.mcc == 'undefined') ? '{}' : '{' + data.mcc.join(',') + '}';
        data.mnc = (typeof data.mnc == 'undefined') ? '{}' : '{' + data.mnc.join(',') + '}';

        PricelistLocation.save(data).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    };
};