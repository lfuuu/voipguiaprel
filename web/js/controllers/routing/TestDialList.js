var TestDialListCtrl = function($scope, TestDial, List, Redirect, $window) {

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
        term_trunk_id: '',
        ignore_region: false
    };

    $scope.displayOnlineResult = false;

    $scope.currentPage = 1;
    $scope.limit = 15;
    $scope.offset = (($scope.currentPage - 1) * $scope.limit);
    $scope.totalItems = 0;

    $scope.init = function(tab) {
        if (tab) tab.title = 'Test Dial';

        $scope.refreshList();
    };

    $scope.refreshList = function() {
        $scope.displayOnlineResult = false;

        TestDial.read({
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
        Redirect.testDialCreate($scope.testGroupId).then(function () {
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

    $scope.runAll = function () {
        if (typeof $scope.list == 'undefined' || $scope.list.length < 1) {
            return;
        }

        $scope.displayOnlineResult = true;

        for (var i in $scope.list) {
            (function (_i) {
                TestDial.result({id: $scope.list[_i].id, displayTreeView: false, ttl: 'none'}).then(function (data) {
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

        Redirect.testDialEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.cloneTest = function(item) {
        if (!userPermissions['test_call_create']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.testDialClone(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.callTest = function (item) {
        $("#call_button_" + item.id).prop('disabled', true);
        function enableCall(callItem) {
            $("#call_button_" + callItem.id).prop('disabled', false);
        }
        setTimeout(enableCall, 2000, item);
        TestDial.call(item.id).then(function (result) {
            // do_nothing
        });
    };

    $scope.deleteItem = function(item)
    {
        if (!$window.confirm('Удалить?')) return;

        TestDial.delete(item.id).then(function(response) {
            $scope.init()
        });
    };

    $scope.setPagingData = function (page) {
        $scope.offset = ((page - 1) * $scope.limit);
        $scope.refreshList();
    };
};