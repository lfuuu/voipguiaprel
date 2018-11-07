var HeaderListCtrl = function ($scope, Header, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'name', 'description', 'value'
    ];

    $scope.init = function (tab) {
        if (tab) tab.title = 'Header';

        Header.read().then(function (data) {
            $scope.list = data;
        });
    };

    $scope.clickCreate = function () {
        Redirect.headerCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function (item) {
        if (!userPermissions['header_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.headerEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function (item) {
        if (!$window.confirm('Удалить?')) return;

        Header.delete(item.id).then(function (response) {
            $scope.init()
        });
    };
};