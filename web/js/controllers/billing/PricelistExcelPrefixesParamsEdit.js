var PricelistExcelPrefixesParamsEditCtrl = function ($scope, params, $modalInstance, $window) {
    $scope.item = {
        id: params.id,
        minimize: false,
        use_ranges: false
    };

    $scope.save = function () {
        window.open(
            '/pricelist/excel-prefixes-new?id=' + $scope.item.id + '&minimize=' + $scope.item.minimize + '&use_ranges=' + $scope.item.use_ranges,
            '_blank'
        );
    }
    
    $scope.back = function () {
        $modalInstance.dismiss();
    }
};