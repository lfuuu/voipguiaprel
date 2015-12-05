var TestAuthListCtrl = function($scope, TestAuth, Redirect, $window) {

    $scope.init = function(tab) {
        if (tab) tab.title = 'Test auth';

        TestAuth.read({server_id: $scope.server.id}).then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.testAuthCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (window.getSelection().type == 'Range') return;

        Redirect.testAuthEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.showTestItem = function(item) {
        if (window.getSelection().type == 'Range') return;

        Redirect.testAuthShowTest(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item)
    {
        if (!$window.confirm('Удалить?')) return;

        TestAuth.delete(item.id).then(function(response) {
            $scope.init()
        });
    };
};