var PricelistListCtrl = function ($scope, Pricelist, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'name', 'orig', 'date_created', 'date_end', 'is_global'
    ];

    $scope.init = function (tab) {
        if (tab) tab.title = 'Pricelist';

        Pricelist.read().then(function (data) {
            $scope.list = data;
        });
    };

    $scope.clickCreate = function () {
        Redirect.pricelistCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function (item) {
        if (!userPermissions['pricelist_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.pricelistShortView(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function (item) {
        if (!$window.confirm('Удалить?')) return;

        Pricelist.delete(item.id).then(function (response) {
            $scope.init()
        });
    };
};