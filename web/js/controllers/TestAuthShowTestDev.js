var TestAuthShowTestDevCtrl = function($scope, TestAuth, params, $modalInstance) {

    $scope.details = 1;

    if (params.id) {
        TestAuth.result({id: params.id, isDev: true, displayTreeView: true}).then(function (data) {
            $scope.item = data.item;
            $scope.isStageRowType = function (row) {
                return row.type == 'STAGE';
            };
            $scope.result_new = data.result_new;
            $scope.full_item = data;
            $scope.key = data.key;
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id
        };
    }

    $scope.descend = function (item) {
        if (item.steps && item.steps.length == 0) {
            TestAuth.descend({'path': item.path, 'key': $scope.key}).then(function (result) {
                var pathArray = item.path.split(',');

                $scope.updateItemRecursively($scope.result_new, pathArray, result.steps);
            });
        }
    };

    $scope.updateItemRecursively = function (item, path, steps) {
        if (path.length > 0) {
            var index = path.shift();
            $scope.updateItemRecursively(item['steps'][index], path, steps);
        } else {
            item.steps = steps;
        }
    };

    $scope.createTest = function (item) {
        var params = {
            server_id: item.server_id,
            trunk_name: item.name,
            dst_number: $scope.item.dst_number,
            src_number: $scope.item.src_number,
            testgroup_id: $scope.item.testgroup_id
        };

        Redirect.testAuthCreateAndFill(params).then(function () {
            $scope.init();
        });
    };

    $scope.clickTrunk = function (item, full_item) {
        var params = {
            server_id: full_item.item.server_id,
            trunk_name: item.back_trunk,
            orig_trunk: item.name,
            trace_to_regions: item.trace_to_regions,
            src_number: full_item.item.src_number,
            dst_number: full_item.item.dst_number,
            src_noa: full_item.item.src_noa,
            dst_noa: full_item.item.dst_noa,
            redirect_number: full_item.item.redirect_number,
            ttl: full_item.item.ttl - 1,
            isDev: true
        };

        TestAuth.trace(params).then(function (result) {
            full_item.trace[result.key] = result;
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };

    $scope.collapseAll = function () {
        $scope.$broadcast('angular-ui-tree:collapse-all');
    };

    $scope.expandAll = function () {
        $scope.$broadcast('angular-ui-tree:expand-all');
    };

    $scope.$on('angular-ui-tree:collapse-all', function () {
        $scope.collapsed = true;
    });

    $scope.$on('angular-ui-tree:expand-all', function () {
        $scope.collapsed = false;
    });
};