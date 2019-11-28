var ApiBillingApiListCtrl = function($scope, ApiBillingApi, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'name', 'server_id', 'api_sig', 'description'
    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'Список API';

        ApiBillingApi.read({'server_id': $scope.server.id}).then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.apiBillingApiCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['api_billing_api_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.apiBillingApiEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        ApiBillingApi.delete(item.id).then(function(response) {
            $scope.init()
        });
    };
};