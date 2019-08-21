var TrunkGroupListCtrl = function($scope, TrunkGroup, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'name', 'server_id'
    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'Группы транков';

        TrunkGroup.read({server_id: $scope.server.id}).then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.trunkGroupCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['trunk_group_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.trunkGroupEdit(item.id).then(function () {
            $scope.init();
        });
    };


    $scope.deleteItem = function(item)
    {
        if (!$window.confirm('Удалить?')) return;

        TrunkGroup.delete(item.id).then(function(response) {
            $scope.init()
        });
    };
};