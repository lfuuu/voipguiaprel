var UserListCtrl = function($scope, User, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'login', 'name'
    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'Пользователи';

        User.read().then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.userCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['user_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.userEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        User.delete({id: item.id}).then(function(response) {
            $scope.init()
        });
    };
};