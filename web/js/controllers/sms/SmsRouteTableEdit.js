var SmsRouteTableEditCtrl = function($rootScope, $scope, Redirect, SmsRouteTable, SmsList, params, $modalInstance, Number) {
    if (params.id) {
        SmsRouteTable.get({id: params.id}).then(function (data) {
            console.log(data);
            $scope.item = data;
        });
    } else {
        $scope.item = {
            name: '',
            server_id: $scope.server.id,
            routes: []
        };
    }

    $scope.sortableOptions = {
        update: function (e, ui) {
            var sortBlocked = false;

            if (ui.item.sortable.index < ui.item.sortable.dropindex) {
                var dropMin = ui.item.sortable.index;
                var dropMax = ui.item.sortable.dropindex;
            } else {
                var dropMin = ui.item.sortable.dropindex;
                var dropMax = ui.item.sortable.index;
            }

            for (var routeKey in $scope.item.routes) {
                if (routeKey >= dropMin && routeKey <= dropMax && $scope.item.routes[routeKey]['is_locked']) {
                    sortBlocked = true;
                }
            }

            if (sortBlocked) {
                ui.item.sortable.cancel();
            }
        },
        axis: 'y'
    };

    $scope.addRoute = function () {
        $scope.item.routes.push({a_number_id: null});
    };

    $scope.removeRoute = function (index) {
        $scope.item.routes.splice(index, 1);
    };

    $scope.save = function () {
        var data = angular.copy($scope.item);

        SmsRouteTable.save(data).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};