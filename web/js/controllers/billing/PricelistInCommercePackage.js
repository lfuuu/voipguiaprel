var PricelistInCommercePackageCtrl = function ($scope, Pricelist, params, $modalInstance, $window) {
    if (params.id) {
        $scope.item = {
            id: params.id,
            item: params.item
        };

        if (params.item.service_type_id == 1) {
            Pricelist.getInCommercePackage({ id: params.id }).then(function (data) {
                $scope.list = data;
                console.log(data);
            });
        } else if (params.item.service_type_id == 3) {
            Pricelist.getInCommercePackageData({ id: params.id }).then(function (data) {
                $scope.list = data;
                console.log(data);
            });
        } else {
            Pricelist.getInCommercePackageSms({ id: params.id }).then(function (data) {
                $scope.list = data;
                console.log(data);
            });
        }

    } else {
        $scope.list = [];
    }

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};