var PricelistGroupListCtrl = function ($scope, PricelistGroup, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'name'
    ];

    $scope.init = function (tab) {
        if (tab) tab.title = 'Группы прайслистов';

        PricelistGroup.read().then(function (data) {
            $scope.list = data;
        });
    };

    $scope.clickCreate = function () {
        Redirect.pricelistGroupCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function (item) {
        if (!userPermissions['pricelist_group_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.pricelistGroupEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function (item) {
        if (!$window.confirm('Удалить?')) return;

        PricelistGroup.delete(item.id).then(function (response) {
            $scope.init()
        });
    };
};