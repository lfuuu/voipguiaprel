var PricelistListCtrl = function ($scope, Pricelist, List, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';
    $scope.hideFilter = false;
    $scope.searchArray = {
        group_id: '',
        query: '',
        service_type_id: '',
        id: '',
        is_active: '',
        is_orig: '',
        currency: '',
    };

    $scope.filterFields = [
        'id', 'name', 'currency_id', 'group_name', 'date_created'
    ];

    $scope.groupId = 'all';

    $scope.currentPage = 1;
    $scope.limit = 15;
    $scope.offset = (($scope.currentPage - 1) * $scope.limit);
    $scope.totalItems = 0;

    $scope.init = function (tab) {
        if (tab) tab.title = 'Pricelist';

        $scope.refreshList();
    };

    $scope.refreshList = function () {
        Pricelist.read({
            search_array: $scope.searchArray,
            offset: $scope.offset,
            limit: $scope.limit
        }).then(function (data) {
            $scope.list = data.data;
            $scope.totalItems = data.totalCount;
        });
    };

    List.pricelistGroup().then(function (data) {
        $scope.groupList = data;
    });

    $scope.currency = List.currency();

    $scope.clickSearch = function () {
        $scope.refreshList();
    };

    $scope.clickCreate = function () {
        Redirect.pricelistCreate($scope.groupId).then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function (item) {
        if (!userPermissions['pricelist_list']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.pricelistShortView(item.id, item.service_type_id).then(function () {
            $scope.init();
        });
    };

    $scope.copyItem = function (item) {
        if (!$window.confirm('Копировать?')) return;

        Pricelist.copy(item.id).then(function (response) {
            $scope.init();

            Redirect.pricelistShortView(response.id, response.service_type_id).then(function () {
                $scope.init();
            });
        });
    };

    $scope.copyAndMultiplyItem = function (item) {
        var multiplier;

        multiplier = parseFloat($window.prompt('Введите множитель для копирования'));

        if (isNaN(multiplier) || multiplier <= 0) return;

        Pricelist.copyAndMultiply(item.id, multiplier).then(function (response) {
            $scope.init();

            Redirect.pricelistShortView(response.id, response.service_type_id).then(function () {
                $scope.init();
            });
        });
    };

    $scope.updatePrefixPricesItem = function (item) {
        var multiplier;
        var dateFrom;
        var dateTo;

        multiplier = parseFloat($window.prompt('Введите множитель для копирования'));
        dateFrom = (new String($window.prompt('Введите дату начала'))).toString();
        dateTo = (new String($window.prompt('Введите дату окончания'))).toString();

        if (isNaN(multiplier) || multiplier <= 0 || !dateTo || !dateFrom) return;

        Pricelist.updatePrefixPrices(item.id, multiplier, dateFrom, dateTo).then(function (response) {
            $scope.init();
            
            Redirect.pricelistShortView(item.id, response.service_type_id).then(function () {
                $scope.init();
            });
        });
    };

    $scope.deleteItem = function (item) {
        if (!$window.confirm('Удалить?')) return;

        Pricelist.delete(item.id).then(function (response) {
            $scope.init()
        });
    };

    $scope.toggleActive = function (item) {
        var text = item.is_active ? 'Деактивировать?' : 'Активировать?';
        if (!$window.confirm(text)) return;

        Pricelist.toggleActive(item.id).then(function (response) {
            $scope.init()
        });
    };

    $scope.setPagingData = function (page) {
        $scope.offset = ((page - 1) * $scope.limit);
        $scope.refreshList();
    };

    $scope.synchronize = function () {
        Pricelist.synchronize().then(function (response) {

        });
    };

    $scope.checkCommerce = function (item) {
        if (item.type_id == 2 && item.service_type_id == 1) {
            Redirect.pricelistInCommerceView(item.id).then(function () {
                $scope.init();
            });
        } else {
            Redirect.pricelistInCommerceViewPackage(item).then(function () {
                $scope.init();
            });
        }
    };

    Pricelist.isTriggerEnabled().then(function (flag) {
        $scope.isTrigger = flag;

    });

    $scope.switchTriggerOn = function () {
        Pricelist.switchTriggerOn().then(function (response){
            $scope.init();
        });
    }
    
    $scope.switchTriggerOff = function () {
        Pricelist.switchTriggerOff().then(function (response) {
            $scope.init();
        });
    }

    $scope.notifyEventToAll = function () {
        Pricelist.notifyEventToAll().then(function (response) {
            $window.alert('Синхронизация завершена');
        });
    };
};