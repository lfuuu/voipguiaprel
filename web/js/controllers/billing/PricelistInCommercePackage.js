var PricelistInCommercePackageCtrl = function ($scope, Pricelist, params, $modalInstance, $window) {
    if (params.id) {
        $scope.item = {
            id: params.id,
            item: params.item
        };
        $scope.link = ($window.location.hostname).includes('.tech') ? 'https://stat.kompaas.tech/' : 'https://stat.mcn.ru/';
        if (params.item.service_type_id == 1) {
            Pricelist.getInCommercePackage({ id: params.id }).then(function (data) {
                $scope.list = data;
            });
        } else if (params.item.service_type_id == 3) {
            Pricelist.getInCommercePackageData({ id: params.id }).then(function (data) {
                $scope.list = data;
            });
        } else {
            Pricelist.getInCommercePackageSms({ id: params.id }).then(function (data) {
                $scope.list = data;
            });
        }

    } else {
        $scope.list = [];
    }

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};