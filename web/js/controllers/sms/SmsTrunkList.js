var SmsTrunkListCtrl = function($scope, SmsTrunk, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'name', 'server_id', 'a2psms_route_table_id', 'route_name',
    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'SMS trunks';
        SmsTrunk.read({server_id: $scope.server.id}).then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.smsTrunkCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['sms_trunk_edit']) {
            return;
        }

        Redirect.smsTrunkEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.openRouteTable = function(routeTableId) {
        if (window.getSelection().type == 'Range') return;

        Redirect.smsRouteTableEdit(routeTableId).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        SmsTrunk.delete(item.id).then(function(response) {
            $scope.init()
        });
    };
};