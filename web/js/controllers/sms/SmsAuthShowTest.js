var SmsTestAuthShowTestCtrl = function($scope, SmsTestAuth, params, $modalInstance, $window) {

    $scope.details = 1;
    $scope.server = { id: 9 };

    if (params.id) {
        SmsTestAuth.result({id: params.id, is_reserve: params.is_reserve}).then(function(data){
            $scope.item = data.item;
            $scope.result = data.result;
            $scope.key = data.key;

            $scope.url = data.url || ($window.location.origin + '/ms' + 9 + '?smsTestAuthShowTest&' + $scope.item.id + '&is_reserve=false');
            $scope.formLink = $window.location.origin + '/ms' + $scope.server.id + '?smsTestAuthShowTest&' + $scope.item.id + '&is_reserve=false';

            console.log('[SmsTestAuthShowTestCtrl] url =', $scope.url);
            console.log('[SmsTestAuthShowTestCtrl] formLink =', $scope.formLink);
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id
        };
    }

    $scope.descend = function (item) {
        if (item.nodes && item.nodes.length == 0) {
            SmsTestAuth.descend({'path': item.path, 'key': $scope.key}).then(function (result) {
                var pathArray = item.path.split(',');
                $scope.updateItemRecursively($scope.result, pathArray, result.nodes);
            });
        }
    };

    $scope.updateItemRecursively = function (item, path, nodes) {
        if (path.length > 0) {
            var index = path.shift();
            $scope.updateItemRecursively(item['nodes'][index], path, nodes);
        } else {
            item.nodes = nodes;
        }
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

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
