var TestCallListCtrl = function($scope, TestCall, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.init = function(tab) {
        if (tab) tab.title = 'Test Call';

        TestCall.read({server_id: $scope.server.id}).then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.testCallCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (window.getSelection().type == 'Range') return;

        Redirect.testCallEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.showTestItem = function(item) {
        if (window.getSelection().type == 'Range') return;

        Redirect.testCallShowTest(item.id).then(function () {
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