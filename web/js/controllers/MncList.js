var MncListCtrl = function ($scope, Mnc, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'mcc', 'mnc', 'country', 'network'
    ];

    $scope.init = function (tab) {
        if (tab) tab.title = 'MNC';

        Mnc.read().then(function (data) {
            $scope.list = data;
        });
    };

    $scope.clickCreate = function () {
        Redirect.mncCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function (item) {
        if (!userPermissions['mnc_list']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.mncEdit(item.mnc, item.mcc).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function (item) {
        if (!$window.confirm('Удалить?')) return;

        Mnc.delete(item).then(function (response) {
            $scope.init()
        });
    };
};