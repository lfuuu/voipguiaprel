var TestCallListCtrl = function($scope, TestCall, List, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.hideFilter = false;
    $scope.filterFields = [
        'name', 'src_trunk_name', 'dst_trunk_name',
        'src_number', 'dst_number', 'redirect_number',
        'cpc', 'connect_time', 'session_time', 'result_online'
    ];

    $scope.searchArray = {
        id: '',
        name: '',
        group_id: '',
        result: '',
        orig_trunk_name: '',
        term_trunk_name: ''
    };

    $scope.displayOnlineResult = false;

    $scope.currentPage = 1;
    $scope.limit = 15;
    $scope.offset = (($scope.currentPage - 1) * $scope.limit);
    $scope.totalItems = 0;

    $scope.init = function(tab) {
        if (tab) tab.title = 'Test Call';

        $scope.refreshList();
    };

    $scope.refreshList = function() {
        $scope.displayOnlineResult = false;

        TestCall.read({
            server_id: $scope.server.id,
            search_array: $scope.searchArray,
            offset: $scope.offset,
            limit: $scope.limit
        }).then(function (data) {
            $scope.list = data.data;
            $scope.totalItems = data.totalCount;
        });
    };

    List.testGroup().then(function (data) {
        $scope.testGroupList = data;
    });

    List.trunkByServer($scope.server.id).then(function (data) {
        $scope.trunkList = data;
    });

    $scope.testResultList = List.testResult();

    $scope.clickSearch = function() {
        $scope.refreshList();
    };

    $scope.clickCreate = function () {
        Redirect.testCallCreate($scope.testGroupId).then(function () {
            $scope.init();
        });
    };

    $scope.testGroupChanged = function(testGroupId) {
        $scope.testGroupId = testGroupId;

        $scope.refreshList();
    };

    $scope.testResultChanged = function(testResult) {
        $scope.testResult = testResult;

        $scope.refreshList();
    };

    $scope.clearCache = function () {
        TestCall.clearCache().then(function (result) {
            if (result.success == 1) {
                alert('Кэш очищен успешно');
            } else {
                alert('Ошибка очистки кэша');
            }
        });
    };

    $scope.runAll = function () {
        if (typeof $scope.list == 'undefined' || $scope.list.length < 1) {
            return;
        }

        $scope.displayOnlineResult = true;

        for (var i in $scope.list) {
            (function (_i) {
                TestCall.result({id: $scope.list[_i].id, displayTreeView: false, ttl: 'none'}).then(function (data) {
                    for (var j in data.result) {
                        if (data.result[j].type === 'RESULT') {
                            $scope.list[_i].result_online = data.result[j].action + (data.result[j].params ? ': ' + data.result[j].params : '');
                        }
                    }
                });
            } (i));
        }
    };

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
    };

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
    };
};