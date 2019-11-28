var ApiBillingApiMethodListCtrl = function($scope, ApiBillingApiMethod, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'name', 'api_id', 'method_sig', 'description'
    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'Список методов API';

        ApiBillingApiMethod.read({'server_id': $scope.server.id}).then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.apiBillingApiMethodCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['api_billing_api_method_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.apiBillingApiMethodEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        ApiBillingApiMethod.delete(item.id).then(function(response) {
            $scope.init()
        });
    };
};