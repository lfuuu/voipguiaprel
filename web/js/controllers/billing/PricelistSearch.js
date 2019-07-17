var PricelistSearchCtrl = function ($scope, Pricelist, Nnp, List, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.hideFilter = false;
    $scope.isLoading = false;
    $scope.noData = false;

    $scope.filterFields = [
        'name', 'server_id'
    ];

    $scope.trunkNameList = [];

    var countryALoadComplete = false;
    var regionALoadComplete = false;
    var cityALoadComplete = false;
    var operatorALoadComplete = false;

    var countryBLoadComplete = false;
    var regionBLoadComplete = false;
    var cityBLoadComplete = false;
    var operatorBLoadComplete = false;
    
    var watchers = {
        a_country_id: function (newValue, oldValue) {
            regionALoadComplete = false;
            operatorALoadComplete = false;
            if (!newValue || newValue.length == 0) {
                $scope.item.a_region_id = null;
                $scope.item.a_city_id = null;
                $scope.item.a_operator_id = null;
                $scope.item.a_ndc_id = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.regionListA = null;
                $scope.operatorListA = null;

                if (countryALoadComplete) {
                    var region = $scope.item.a_region_id;
                    var city = $scope.item.a_city_id;
                    var operator = $scope.item.a_operator_id;
                }

                Nnp.regionList({country_code: newValue}).then(function (data) {
                    $scope.regionListA = data;

                    if (countryALoadComplete) {
                        $scope.item.a_region_id = region;
                        $scope.item.a_city_id = city;
                        regionALoadComplete = true;
                    }
                });

                Nnp.operatorList({country_code: newValue}).then(function (data) {
                    $scope.operatorListA = data;

                    if (countryALoadComplete) {
                        $scope.item.a_operator_id = operator;
                        operatorALoadComplete = true;
                    }
                });
            }
        },
        b_country_id: function (newValue, oldValue) {
            regionBLoadComplete = false;
            operatorBLoadComplete = false;
            if (!newValue || newValue.length == 0) {
                $scope.item.b_region_id = null;
                $scope.item.b_city_id = null;
                $scope.item.b_operator_id = null;
                $scope.item.b_ndc_id = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.regionListB = null;
                $scope.operatorListB = null;

                if (countryBLoadComplete) {
                    var region = $scope.item.b_region_id;
                    var city = $scope.item.b_city_id;
                    var operator = $scope.item.b_operator_id;
                }

                Nnp.regionList({country_code: newValue}).then(function (data) {
                    $scope.regionListB = data;

                    if (countryBLoadComplete) {
                        $scope.item.b_region_id = region;
                        $scope.item.b_city_id = city;
                        regionBLoadComplete = true;
                    }
                });

                Nnp.operatorList({country_code: newValue}).then(function (data) {
                    $scope.operatorListB = data;

                    if (countryBLoadComplete) {
                        $scope.item.b_operator_id = operator;
                        operatorBLoadComplete = true;
                    }
                });
            }
        },
        a_region_id: function (newValue, oldValue) {
            cityALoadComplete = false;
            if (!newValue || newValue.length == 0) {
                $scope.item.a_city_id = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.cityListA = null;

                var city = $scope.item.a_city_id;

                Nnp.cityList({country_code: $scope.item.a_country_id, region: newValue}).then(function (data) {
                    $scope.cityListA = data;
                    $scope.cities = data;

                    $scope.item.a_city_id = city;
                    cityALoadComplete = true;
                });
            }
        },
        b_region_id: function (newValue, oldValue) {
            cityBLoadComplete = false;
            if (!newValue || newValue.length == 0) {
                $scope.item.b_city_id = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.cityListB = null;

                var city = $scope.item.b_city_id;

                Nnp.cityList({country_code: $scope.item.b_country_id, region: newValue}).then(function (data) {
                    $scope.cityListB = data;
                    $scope.cities = data;

                    $scope.item.b_city_id = city;
                    cityBLoadComplete = true;
                });
            }
        }
    };

    $scope.init = function (tab) {
        if (tab) tab.title = 'Отчет по CDR';

        $scope.list = [];

        var date = new Date();

        var dateText = date.toISOString().slice(0, 19).replace('T', ' ');
        
        $scope.item = {
            a_country_id: '',
            b_country_id: '',
            a_region_id: '',
            b_region_id: '',
            a_city_id: '',
            b_city_id: '',
            a_operator_id: '',
            b_operator_id: '',
            a_ndc_id: '',
            b_ndc_id: '',
            timestamp: dateText,
            number_a: '',
            number_b: '',
            mcc: '',
            mnc: '',
            location_id: '',
            is_orig: false
        };

        $scope.$watch('item.a_country_id', watchers.a_country_id);
        $scope.$watch('item.b_country_id', watchers.b_country_id);
        $scope.$watch('item.a_region_id', watchers.a_region_id);
        $scope.$watch('item.b_region_id', watchers.b_region_id);
    };

    Nnp.countryList().then(function (data) {
        $scope.countryListA = data;
        $scope.countryListB = data;
        countryALoadComplete = true;
        countryBLoadComplete = true;
    });

    Nnp.ndcTypeList().then(function (data) {
        $scope.ndcTypeList = data;
    });

    $scope.clickSearch = function () {
        $scope.isLoading = true;
        $scope.noData = false;
        Pricelist.search($scope.item).then(function (data) {
            $scope.isLoading = false;
            $scope.list = [];
            $scope.processData(data);
        });
    };

    $scope.processData = function (data) {
        for (var i in data.paths) {
            for (var j in data.paths[i]) {
                if (j == 'extra_count') {
                    $scope.list.push({pricelist_id: 'Еще ' + data.paths[i][j] + '...'});
                } else {
                    $scope.list.push(data.paths[i][j]);
                }
            }
        }
    };

    $scope.locationList = List.location();

    List.hub().then(function (data) {
        $scope.hubList = data;
    });

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