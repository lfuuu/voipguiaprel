var TestCallListCtrl = function($scope, TestCall, List, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';
    $scope.testGroupId = 'undefined';
    $scope.testResult = 'undefined';

    $scope.currentPage = 1;
    $scope.limit = 15;
    $scope.offset = (($scope.currentPage - 1) * $scope.limit);
    $scope.totalItems = 0;

    $scope.init = function(tab) {
        if (tab) tab.title = 'Test Call';

        $scope.refreshList();
    };

    $scope.refreshList = function() {
        if ($scope.testGroupId == 'undefined') {
            return;
        }

        TestCall.read({
            server_id: $scope.server.id,
            test_group_id: $scope.testGroupId,
            test_result: $scope.testResult,
            offset: $scope.offset,
            limit: $scope.limit
        }).then(function (data) {
            $scope.list = data.data;
            $scope.totalItems = data.totalCount;
        });
    }

    List.testGroup().then(function (data) {
        $scope.testGroupList = data;
    });

    $scope.testResultList = List.testResult();

    $scope.clickCreate = function () {
        Redirect.testCallCreate($scope.testGroupId).then(function () {
            $scope.init();
        });
    };

    $scope.testGroupChanged = function(testGroupId) {
        $scope.testGroupId = testGroupId;

        $scope.refreshList();
    }

    $scope.testResultChanged = function(testResult) {
        $scope.testResult = testResult;

        $scope.refreshList();
    }

    $scope.clickItem = function(item) {
        if (!userPermissions['test_call_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.testCallEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.cloneTest = function(item) {
        if (!userPermissions['test_call_create']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.testCallClone(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.toggleDisplayTreeView = function(item) {
        item.displayTreeView = !item.displayTreeView;
    }

    $scope.showTestPrimary = function (item) {
        $scope.showTestBasic(item, 'testCallShowTest');
    };

    $scope.showTestReserve = function (item) {
        $scope.showTestBasic(item, 'testCallShowTestReserve');
    };

    $scope.showTestReserve2 = function (item) {
        $scope.showTestBasic(item, 'testCallShowTestReserve2');
    };

    $scope.showTestDev = function (item) {
        $scope.showTestBasic(item, 'testCallShowTestDev');
    };

    $scope.showTestBasic = function (item, method) {
        if (window.getSelection().type == 'Range') {
            return;
        }

        if (typeof item.displayTreeView == 'undefined') {
            item.displayTreeView = false;
        }

        Redirect[method](item.id, item.displayTreeView).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item)
    {
        if (!$window.confirm('Удалить?')) return;

        TestCall.delete(item.id).then(function(response) {
            $scope.init()
        });
    };

    $scope.setPagingData = function (page) {
        $scope.offset = ((page - 1) * $scope.limit);
        $scope.refreshList();
    }
};