var RoleListCtrl = function($scope, Role, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'name', 'description'
    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'Роли';

        Role.read().then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.roleCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['role_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.roleEdit(item.name).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        Role.delete({name: item.name}).then(function(response) {
            $scope.init()
        });
    };
};