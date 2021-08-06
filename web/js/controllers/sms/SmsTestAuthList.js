var SmsTestAuthListCtrl = function($scope, SmsTestAuth, SmsList, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'name', 'trunk_name'
    ];
    
    $scope.searchArray = {
        id: '',
        name: '',
        group_id: '',
        trunk_name: '',
    };
    
    $scope.init = function (tab) {
        if (tab) tab.title = 'Тесты маршрутизации';

        $scope.refreshList();
    };

    $scope.refreshList = function() {
        SmsTestAuth.read({
                server_id: $scope.server.id,
                search_array: $scope.searchArray,
            }).then(function (data) {
            $scope.list = data;
        });
    };

    SmsList.testGroup({}).then(function (data) {
        $scope.testGroupList = data;
    });
    
    $scope.clickCreate = function() {
        Redirect.smsTestAuthCreate().then(function () {
            $scope.init();
        });
    };

    SmsList.trunkByServer($scope.server.id).then(function (data) {
        $scope.trunkList = data;
    });

    $scope.showTestPrimary = function (item) {
        $scope.showTestBasic(item, 'smsTestAuthShowTest');
    };
    
    // $scope.showTestReserve = function (item) {
    //     $scope.showTestBasic(item, 'smsTestAuthShowTestReserve');
    // };

    $scope.showTestBasic = function (item, method) {
        if (window.getSelection().type == 'Range') {
            return;
        }

        Redirect[method](item.id).then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        // if (!userPermissions['sms_test_auth_edit']) {
        //     return;
        // }

        if (window.getSelection().type == 'Range') return;

        Redirect.smsTestAuthEdit(item.id).then(function () {
            $scope.init();
        });
    };

    // $scope.cloneTest = function(item) {
    //     if (!userPermissions['sms_test_auth_create']) {
    //         return;
    //     }

    //     if (window.getSelection().type == 'Range') return;

    //     Redirect.smsTestAuthClone(item.id).then(function () {
    //         $scope.init();
    //     });
    // };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        SmsTestAuth.delete(item.id).then(function(response) {
            $scope.init()
        });
    };
};