var TestAuthListCtrl = function($scope, TestAuth, Scripts, List, Redirect, $window) {

    $scope.TEST_GROUP_AUTOMATIC = 2;

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'name', 'trunk_name', 'src_number',
        'dst_number', 'redirect_number', 'cpc',
        'result_online'
    ];

    $scope.testGroupId = 'undefined';
    $scope.testResult = 'undefined';
    $scope.displayOnlineResult = false;

    $scope.currentPage = 1;
    $scope.limit = 15;
    $scope.offset = (($scope.currentPage - 1) * $scope.limit);
    $scope.totalItems = 0;

    $scope.init = function (tab) {
        if (tab) tab.title = 'Test auth';

        $scope.refreshList();
    };

    $scope.refreshList = function() {
        if ($scope.testGroupId == 'undefined') {
            return;
        }

        $scope.displayOnlineResult = false;

        TestAuth.read({
                server_id: $scope.server.id,
                test_group_id: $scope.testGroupId,
                test_result: $scope.testResult,
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

    $scope.testResultList = List.testResult();

    $scope.clickCreate = function () {
        Redirect.testAuthCreate($scope.testGroupId).then(function () {
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
        TestAuth.clearCache().then(function (result) {
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
                TestAuth.result({id: $scope.list[_i].id, displayTreeView: false, ttl: 'none'}).then(function (data) {
                    for (var j in data.result) {
                        if (data.result[j].type === 'RESULT') {
                            $scope.list[_i].result_online = data.result[j].action + (data.result[j].params ? ': ' + data.result[j].params : '');
                        }
                    }
                });
            } (i));
        }
    };

    $scope.generateTests = function () {
        Scripts.generateTests().then(function (result) {
            if (result.success == 1) {
                alert('Создание тестов успешно инициировано.');
            } else {
                alert('Ошибка создания тестов.');
            }
        });
    };

    $scope.deleteTests = function () {
        Scripts.deleteTests().then(function (result) {
            if (result.success == 1) {
                alert('Удаление тестов успешно инициировано.');
            } else {
                alert('Ошибка удаления тестов.');
            }
        });
    };

    $scope.viewTestsLog = function () {
        Redirect.testGenerateLog().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function (item) {
        if (!userPermissions['test_auth_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') {
            return;
        }

        Redirect.testAuthEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.cloneTest = function(item) {
        if (!userPermissions['test_auth_create']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.testAuthClone(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.toggleTTL = function(item) {
        item.ttl = !item.ttl;
    };

    $scope.showTestPrimary = function (item) {
        $scope.showTestBasic(item, 'testAuthShowTestPrimary');
    };

    $scope.showTestReserve = function (item) {
        $scope.showTestBasic(item, 'testAuthShowTestReserve');
    };

    $scope.showTestReserve2 = function (item) {
        $scope.showTestBasic(item, 'testAuthShowTestReserve2');
    };

    $scope.showTestDev = function (item) {
        $scope.showTestBasic(item, 'testAuthShowTestDev');
    };

    $scope.showTestBasic = function (item, method) {
        if (window.getSelection().type == 'Range') {
            return;
        }

        if (typeof item.ttl == 'undefined') {
            item.ttl = false;
        }

        Redirect[method](item.id, item.ttl).then(function () {
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

    $scope.setPagingData = function (page) {
        $scope.offset = ((page - 1) * $scope.limit);
        $scope.refreshList();
    }
};