var CamelRouteTableListCtrl = function($scope, CamelRouteTable, Redirect, $window) {

    $scope.sortType = 'trunk_name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'gt', 'oper', 'country', 'area', 'loc'
    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'Таблицы маршрутизации';

        CamelRouteTable.read({server_id: $scope.server.id}).then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.camelRouteTableCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['camel_route_table_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.camelRouteTableEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        CamelRouteTable.delete(item.id).then(function(response) {
            $scope.init()
        });
    };
};