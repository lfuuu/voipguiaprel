var PricelistShortViewInternetCtrl = function ($scope, Redirect, List, Pricelist, PricelistLocation, PricelistFilterA, PricelistFilterB, PricelistPrefixPrice, params, $modalInstance, $window) {

    $scope.locationIds = List.location();

    $scope.drawTable = function (data) {
        $scope.list = data;
    };

    $scope.initData = function (id) {
        Pricelist.getWithDependentsNew({id: id, type: 'short'}).then(function (data) {
            $scope.item = {};
            $scope.item.id = data[0]['id'];
            $scope.item.date_start = data[0]['date_start'];
        
            $scope.pricelistIsActive = data[0]['is_active'];
            $scope.pricelistDateStart = data[0]['date_start'];
            $scope.pricelistServiceTypeId = data[0]['service_type_id'];
            $scope.pricelistName = data[0]['name'];
            
            $scope.drawTable(data);
        });
    };

    if (params.id) {
        $scope.initData(params.id);
    } else {
        $scope.item = {};
    }

    $scope.back = function () {
        $modalInstance.dismiss();
    };

    $scope.viewPricelist = function (id) {
        Redirect.pricelistView(id).then(function () {
            $scope.initData($scope.item.id);
        }, function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.editLocation = function (id) {
        Redirect.pricelistLocationEdit(id, $scope.pricelistIsActive, 3).then(function () {
            $scope.initData($scope.item.id);
        }, function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.editPrefixPrice = function (id) {
        Redirect.pricelistPrefixPriceEdit(id, $scope.pricelistIsActive, $scope.item.id).then(function () {
            $scope.initData($scope.item.id);
        }, function () {
            $scope.initData($scope.item.id);
        });
    };
};