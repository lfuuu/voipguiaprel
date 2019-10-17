var CamelRouteTableEditCtrl = function($rootScope, $scope, Redirect, CamelRouteTable, params, $modalInstance) {
    if (params.id) {
        CamelRouteTable.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            name: ''
        };
    }

    $scope.save = function () {
        CamelRouteTable.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
