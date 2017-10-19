var TestAuthListCtrl = function($scope, TestAuth, List, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';
    $scope.testGroupId = undefined;

    $scope.init = function (tab) {
        if (tab) tab.title = 'Test auth';

        TestAuth.read({server_id: $scope.server.id}).then(function (data) {
            $scope.list = data;
        });
    };

    List.testGroup().then(function (data) {
        $scope.testGroupList = data;
    });

    $scope.clickCreate = function () {
        Redirect.testAuthCreate($scope.testGroupId).then(function () {
            $scope.init();
        });
    };

    $scope.testGroupChanged = function(testGroupId) {
        $scope.testGroupId = testGroupId;
    }

    $scope.clickItem = function (item) {
        if (window.getSelection().type == 'Range') {
            return;
        }

        Redirect.testAuthEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.showTestPrimary = function (item) {
        if (window.getSelection().type == 'Range') {
            return;
        }

        Redirect.testAuthShowTestPrimary(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.showTestReserve = function (item) {
        if (window.getSelection().type == 'Range') {
            return;
        }

        Redirect.testAuthShowTestReserve(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function (item) {
        if (!$window.confirm('Удалить?')) {
            return;
        }

        TestAuth.delete(item.id).then(function () {
            $scope.init()
        });
    };
};