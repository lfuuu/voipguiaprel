var CamelTestAuthListCtrl = function($scope, CamelTestAuth, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'name'
    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'Тесты маршрутизации';

        CamelTestAuth.read({server_id: $scope.server.id}).then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.camelTestAuthCreate().then(function () {
            $scope.init();
        });
    };

    $scope.showTestPrimary = function (item) {
        $scope.showTestBasic(item, 'camelTestAuthShowTest');
    };

    $scope.showTestBasic = function (item, method) {
        if (window.getSelection().type == 'Range') {
            return;
        }

        Redirect[method](item.id).then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['camel_test_auth_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.camelTestAuthEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.cloneTest = function(item) {
        if (!userPermissions['camel_test_auth_create']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.camelTestAuthClone(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        CamelTestAuth.delete(item.id).then(function(response) {
            $scope.init()
        });
    };
};