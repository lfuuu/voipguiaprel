var TrunkListCtrl = function($scope, Trunk, Redirect, $window) {

    $scope.sortType = 'trunk_name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'trunk_name', 'name',
        'capacity', 'load_warning',
        'road_to_regions', 'server_id'
    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'Транки';

        Trunk.read({server_id: $scope.server.id}).then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.trunkCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['trunk_list']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.trunkEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.openRouteTable = function(routeTableId) {
        if (window.getSelection().type == 'Range') return;

        Redirect.routeTableEdit(routeTableId).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        Trunk.delete(item.id).then(function(response) {
            $scope.init()
        });
    };

    $scope.fullInfoItem = function(item) {
        $window.open('/trunk/full-info?trunkId=' + item.id);
    };
};