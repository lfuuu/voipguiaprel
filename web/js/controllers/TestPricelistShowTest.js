var TestPricelistShowTestCtrl = function($scope, TestPricelist, Redirect, params, $modalInstance) {
    if (params.id) {
        TestPricelist.result({id: params.id, server_id: $scope.server.id}).then(function (data) {
            $scope.item = data;
        });
    }

    $scope.collapseAll = function () {
        $scope.$broadcast('angular-ui-tree:collapse-all');
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};