var CamelTrunkListCtrl = function($scope, CamelTrunk, Redirect, $window) {

    $scope.sortType = 'trunk_name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'name', 'gt_id', 'camel_route_table_id'
    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'Транки';

        CamelTrunk.read({server_id: $scope.server.id}).then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.camelTrunkCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['camel_trunk_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.camelTrunkEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.openRouteTable = function(routeTableId) {
        if (window.getSelection().type == 'Range') return;

        Redirect.camelRouteTableEdit(routeTableId).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        CamelTrunk.delete(item.id).then(function(response) {
            $scope.init()
        });
    };

    $scope.copyItem = function (item) {
        if (!$window.confirm('Копировать?')) return;
        Redirect.camelTrunkCreate(item.id).then(function () {
            $scope.init();
        });
    };
};