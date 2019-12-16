var CamelRouteTableEditCtrl = function($rootScope, $scope, Redirect, CamelRouteTable, CamelList, Number, params, $modalInstance) {
    if (params.id) {
        CamelRouteTable.get({id: params.id}).then(function (data) {
            for (var i in data.routes) {
                if (data.routes[i].b_number_regexp) {
                    data.routes[i].b_number_regexp_or_select = true;
                } else {
                    data.routes[i].b_number_regexp_or_select = false;
                }
            }

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
        $scope.item.routes.push({a_number_id: null, b_number_regexp: null, gt_id: null, outcome_id: null, b_number_regexp_or_select: true});
    };

    $scope.removeRoute = function (index) {
        $scope.item.routes.splice(index, 1);
    };

    $scope.save = function () {
        var data = angular.copy($scope.item);

        for (var i in data.routes) {
            if (data.routes[i].b_number_regexp_or_select) {
                delete data.routes[i].b_number_id;
            } else {
                delete data.routes[i].b_number_regexp;
            }
        }

        CamelRouteTable.save(data).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
