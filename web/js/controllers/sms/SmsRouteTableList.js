var SmsRouteTableListCtrl = function($scope, SmsRouteTable, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [

    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'Таблицы маршрутизации';

        SmsRouteTable.read({server_id: $scope.server.id}).then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.smsRouteTableCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['camel_route_table_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.smsRouteTableEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        SmsRouteTable.delete(item.id).then(function(response) {
            $scope.init()
        });
    };
};