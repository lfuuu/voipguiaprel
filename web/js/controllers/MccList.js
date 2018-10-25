var MccListCtrl = function ($scope, Mcc, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'mcc', 'country', 'iso', 'country_code'
    ];

    $scope.init = function (tab) {
        if (tab) tab.title = 'MCC';

        Mcc.read().then(function (data) {
            $scope.list = data;
        });
    };

    $scope.clickCreate = function () {
        Redirect.mccCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function (item) {
        if (!userPermissions['mcc_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.mccEdit(item.mcc).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function (item) {
        if (!$window.confirm('Удалить?')) return;

        Mcc.delete(item.mcc).then(function (response) {
            $scope.init()
        });
    };
};