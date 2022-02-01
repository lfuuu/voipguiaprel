var ApiBillingApiArchivePricelistListCtrl = function($scope, ApiBillingApiPricelist, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'name'
    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'Список прайслистов';

        ApiBillingApiPricelist.readArchive().then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.apiBillingApiPricelistCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['api_billing_api_pricelist_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.apiBillingApiPricelistEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.restoreItem = function(item) {
        if (!$window.confirm('Восстановить?')) return;

        ApiBillingApiPricelist.restore(item.id).then(function(response) {
            $scope.init()
        });
    };
};