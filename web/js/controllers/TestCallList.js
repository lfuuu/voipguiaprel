var TestCallListCtrl = function($scope, TestCall, List, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';
    $scope.testGroupId = undefined;

    $scope.init = function(tab) {
        if (tab) tab.title = 'Test Call';

        TestCall.read({server_id: $scope.server.id}).then(function(data){
            $scope.list = data;
        });
    };

    List.testGroup().then(function (data) {
        $scope.testGroupList = data;
    });

    $scope.clickCreate = function() {
        Redirect.testCallCreate($scope.testGroupId).then(function () {
            $scope.init();
        });
    };

    $scope.testGroupChanged = function(testGroupId) {
        $scope.testGroupId = testGroupId;
    }

    $scope.clickItem = function(item) {
        if (window.getSelection().type == 'Range') return;

        Redirect.testCallEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.toggleDisplayTreeView = function(item) {
        item.displayTreeView = !item.displayTreeView;
    }

    $scope.showTestItem = function(item) {
        if (window.getSelection().type == 'Range') return;

        if (typeof item.displayTreeView == 'undefined') {
            item.displayTreeView = false;
        }

        Redirect.testCallShowTest(item.id, item.displayTreeView).then(function () {
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
};