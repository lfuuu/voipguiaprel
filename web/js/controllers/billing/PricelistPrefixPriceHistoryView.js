var PricelistPrefixPriceHistoryViewCtrl = function($rootScope, $scope, PricelistPrefixPriceHistoryItem, params, $modalInstance) {
    if (params.id) {
        PricelistPrefixPriceHistoryItem.read({pricelist_prefix_price_history_id: params.id}).then(function (data) {
            $scope.list = data;
        });
    } else {
        $scope.list = [];
    }
    
    $scope.map = {
        'decrease': 'Уменьшение цены',
        'increase': 'Увеличение цены',
        'delete': 'Удаление кода',
        'new': 'Новый код',
        'prolong': 'Продление кода',
        'skipped': 'Пропущен',
    };
    
    $scope.styleMap = {
        'decrease': 'font-weight: bold; background-color: #99ff99;',
        'increase': 'font-weight: bold; background-color: #ff9999;',
        'delete': 'font-weight: bold; background-color: lightgrey;',
        'new': 'font-weight: bold; background-color: lightblue;',
        'prolong': 'font-weight: bold; background-color: white;',
        'skipeed': 'font-weight: bold; background-color: white;',
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
