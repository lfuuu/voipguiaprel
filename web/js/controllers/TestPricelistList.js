var TestPricelistListCtrl = function($scope, TestPricelist, Scripts, List, Redirect, $window) {
    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'name', 'trunk_name', 'src_number',
        'dst_number', 'redirect_number', 'cpc',
        'result_online'
    ];

    $scope.init = function (tab) {
        if (tab) tab.title = 'Test pricelist';

        $scope.refreshList();
    };

    $scope.locationList = List.location();

    $scope.refreshList = function() {
        TestPricelist.read().then(function (data) {
            $scope.list = data;
        });
    };

    $scope.clickCreate = function () {
        Redirect.testPricelistCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function (item) {
        if (!userPermissions['test_pricelist_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') {
            return;
        }

        Redirect.testPricelistEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.showTestPrimary = function (item) {
        $scope.showTestBasic(item, 'testPricelistShowTest');
    };

    $scope.showTestBasic = function (item, method) {
        if (window.getSelection().type == 'Range') {
            return;
        }

        Redirect[method](item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function (item) {
        if (!$window.confirm('Удалить?')) {
            return;
        }

        TestPricelist.delete(item.id).then(function () {
            $scope.init()
        });
    };
};