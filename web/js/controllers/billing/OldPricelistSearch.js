var OldPricelistSearchCtrl = function ($scope, Pricelist, Nnp, List, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.hideFilter = false;
    $scope.isLoading = false;
    $scope.noData = false;

    $scope.filterFields = [
        'name', 'server_id'
    ];

    $scope.init = function (tab) {
        if (tab) tab.title = 'Отчет по CDR';

        $scope.list = [];

        $scope.item = {
            country_code: '',
            prefix: '',
            pricelist_ids: null,
            exact_match: false
        };
    };

    Nnp.countryList().then(function (data) {
        $scope.countryList = data;
    });

    List.pricelist().then(function (data) {
        $scope.pricelistList = data;
    });

    $scope.clickSearch = function () {
        $scope.isLoading = true;
        $scope.noData = false;
        Pricelist.oldSearch($scope.item).then(function (data) {
            $scope.isLoading = false;
            $scope.list = [];
            $scope.processData(data);
        });
    };

    $scope.processData = function (data) {
        $scope.list = data.paths;
    };

    $scope.clickPricelist = function (item) {
        if (item.pricelist_id + 0 === item.pricelist_id) {
            Redirect.pricelistShortView(item.pricelist_id);
        }
    };

    $scope.clickLocation = function (item) {
        if (item.pricelist_id + 0 === item.pricelist_id) {
            Redirect.pricelistSearchView(item.pricelist_id, 'location', item.location_id);
        }
    };

    $scope.clickFilterA = function (item) {
        if (item.pricelist_id + 0 === item.pricelist_id) {
            Redirect.pricelistSearchView(item.pricelist_id, 'filterA', item.filter_a);
        }
    };

    $scope.clickFilterB = function (item) {
        if (item.pricelist_id + 0 === item.pricelist_id) {
            Redirect.pricelistSearchView(item.pricelist_id, 'filterB', item.filter_b);
        }
    };

    $scope.clickPrefixPrice = function (item) {
        if (item.pricelist_id + 0 === item.pricelist_id) {
            Redirect.pricelistSearchView(item.pricelist_id, 'prefix', item.prefix);
        }
    };
};