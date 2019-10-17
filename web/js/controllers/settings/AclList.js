var AclListCtrl = function ($scope, Acl, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'name', 'description'
    ];

    $scope.init = function (tab) {
        if (tab) tab.title = 'Права доступа';
        $scope.refreshList();
    };

    $scope.refreshList = function() {
        Acl.read().then(function (data) {
            $scope.list = data;
        });
    };
};