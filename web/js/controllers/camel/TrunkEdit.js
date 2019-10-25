var CamelTrunkEditCtrl = function($rootScope, $scope, Redirect, CamelTrunk, CamelList, Prefixlist, params, $modalInstance) {
    if (params.id) {
        CamelTrunk.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            name: '',
            server_id: $scope.server.id
        };
    }

    Prefixlist.listByType({type_id: 14}).then(function (data) {
        $scope.prefixlistList = data;
    });

    CamelList.routeTable({server_id: $scope.server.id}).then(function (data) {
        $scope.routeTableList = data;
    });

    $scope.save = function () {
        CamelTrunk.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
