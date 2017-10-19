var TestGroupListCtrl = function($scope, TestGroup, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.init = function (tab) {
        if (tab) tab.title = 'Test groups';

        TestGroup.read().then(function (data) {
            $scope.list = data;
        });
    };

    $scope.clickCreate = function () {
        Redirect.testGroupCreate($scope.testGroupId).then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function (item) {
        if (window.getSelection().type == 'Range') {
            return;
        }

        Redirect.testGroupEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function (item) {
        if (!$window.confirm('Удалить?')) {
            return;
        }

        TestGroup.delete(item.id).then(function () {
            $scope.init()
        });
    };
};