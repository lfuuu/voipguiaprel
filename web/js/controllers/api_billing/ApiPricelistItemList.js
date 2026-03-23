var ApiBillingApiPricelistItemListCtrl = function($scope, ApiBillingApiPricelistItem, Redirect, $window) {

    $scope.sortType = 'id';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'name', 'pricelist_id', 'api_id', 'api_method_id', 'enabled', 'price', 'price_currency_id', 'rate', 'cost_currency_id', 'is_active'
    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'Список элементов прайслистов';

        ApiBillingApiPricelistItem.read({server_id: $scope.server.id}).then(function(data){
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.apiBillingApiPricelistItemCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['api_billing_api_pricelist_item_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.apiBillingApiPricelistItemEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;

        ApiBillingApiPricelistItem.delete(item.id).then(function(response) {
            $scope.init()
        });
    };
};
