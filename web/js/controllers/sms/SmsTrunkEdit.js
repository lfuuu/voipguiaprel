
var SmsTrunkEditCtrl = function($rootScope, $scope, Redirect, SmsTrunk, SmsRouteTable, SmsList, params, $modalInstance) {
var SERVER_ID = 9;

    if (params.id) {
        SmsTrunk.get({id: params.id}).then(function (data) {
            $scope.item = data;

        });
    } else {
        $scope.item = {
            name: '',
            route_name: '',
            server_id: $scope.server.id,
        };
    }

    SmsList.routeTable({server_id: SERVER_ID}).then(function (data) {
        $scope.routeTableList = data;

    });

    $scope.save = function () {      
        console.log($scope.item);
        SmsTrunk.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
