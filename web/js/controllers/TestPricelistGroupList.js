var TestPricelistGroupListCtrl = function($scope, TestPricelistGroup, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
      'id', 'name'
    ];

    $scope.init = function (tab) {
        if (tab) tab.title = 'Test groups';

        TestPricelistGroup.read().then(function (data) {
            $scope.list = data;
        });
    };

    $scope.clickCreate = function () {
        Redirect.testPricelistGroupCreate($scope.testGroupId).then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function (item) {
        if (!userPermissions['test_pricelist_group_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') {
            return;
        }

        Redirect.testPricelistGroupEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function (item) {
        if (!$window.confirm('Удалить?')) {
            return;
        }

        TestPricelistGroup.delete(item.id).then(function () {
            $scope.init()
        });
    };
};