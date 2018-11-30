var PricelistFilterAEditCtrl = function($scope, $rootScope, Major, PricelistFilterA, Nnp, List, params, $modalInstance, $window, Redirect) {

    $scope.NNP_MODE_FILTER = 1;
    $scope.NNP_MODE_PARAMETERS = 2;

    var countryLoadComplete = false;
    var regionLoadComplete = false;
    var cityLoadComplete = false;
    var operatorLoadComplete = false;

    var watchers = {
        filter_country: function (newValue, oldValue) {
            if (newValue !== oldValue) {
                Major.read({country_code: newValue}).then(function (result) {
                    $scope.filterList = result;
                });
            }
        },
        nnp_country: function (newValue, oldValue) {
            regionLoadComplete = false;
            operatorLoadComplete = false;
            if (!newValue || newValue.length == 0) {
                $scope.item.nnp_region = null;
                $scope.item.nnp_city = null;
                $scope.item.nnp_operator = null;
                $scope.item.nnp_ndc_type = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.regionList = null;
                $scope.operatorList = null;

                if (countryLoadComplete) {
                    var region = $scope.item.nnp_region;
                    var city = $scope.item.nnp_city;
                    var operator = $scope.item.nnp_operator;
                }

                Nnp.regionList({country_code: newValue}).then(function (data) {
                    $scope.regionList = data;

                    if (countryLoadComplete) {
                        $scope.item.nnp_region = region;
                        $scope.item.nnp_city = city;
                        regionLoadComplete = true;
                    }
                });

                Nnp.operatorList({country_code: newValue}).then(function (data) {
                    $scope.operatorList = data;

                    if (countryLoadComplete) {
                        $scope.item.nnp_operator = operator;
                        operatorLoadComplete = true;
                    }
                });
            }
        },
        nnp_region: function (newValue, oldValue) {
            cityLoadComplete = false;

            if (!newValue || newValue.length == 0) {
                $scope.item.nnp_city = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.cityList = null;

                var city = $scope.item.nnp_city;

                Nnp.cityList({country_code: $scope.item.nnp_country, region: newValue}).then(function (data) {
                    $scope.cityList = data;
                    $scope.cities = data;

                    $scope.item.nnp_city = city;
                    cityLoadComplete = true;
                });
            }
        }
    };

    $scope.nnpDataParseError = false;
    $scope.nnpProcessed = false;
    $scope.nnpProcessFailed = false;
    $scope.nnpProcessComplete = false;

    $scope.nnpMode = $scope.NNP_MODE_PARAMETERS;

    $scope.pricelistIsActive = params.pricelist_is_active;

    if (params.id) {
        PricelistFilterA.get({id: params.id}).then(function (data) {
            $scope.item = data;

            $scope.setNnpFields(data);

            if (data.nnp_filter && data.filter_country) {
                Major.read({country_code: data.filter_country}).then(function (result) {
                    $scope.filterList = result;
                });
            }

            $scope.$watch('item.nnp_country', watchers.nnp_country);
            $scope.$watch('item.nnp_region', watchers.nnp_region);
            $scope.$watch('item.filter_country', watchers.filter_country);
        });
    } else if (params.location_id) {
        $scope.item = {
            pricelist_location_id: params.location_id,
            nnp_country: null,
            nnp_city: null,
            nnp_region: null,
            nnp_operator: null,
            nnp_ndc_type: null,
            mode_selected: true,
            filter_country: 643
        };

        $scope.$watch('item.nnp_country', watchers.nnp_country);
        $scope.$watch('item.nnp_region', watchers.nnp_region);
        $scope.$watch('item.filter_country', watchers.filter_country);
    } else {
        $scope.item = {
            nnp_country: null,
            nnp_city: null,
            nnp_region: null,
            nnp_operator: null,
            nnp_ndc_type: null,
            mode_selected: true,
            filter_country: 643
        };

        $scope.$watch('item.nnp_country', watchers.nnp_country);
        $scope.$watch('item.nnp_region', watchers.nnp_region);
        $scope.$watch('item.filter_country', watchers.filter_country);
    }

    $scope.setNnpMode = function(nnpMode) {
        $scope.nnpMode = nnpMode;
    };

    Nnp.countryList().then(function (data) {
        $scope.countryList = data;
        countryLoadComplete = true;
    });

    Nnp.ndcTypeList().then(function (data) {
        $scope.ndcTypeList = data;
    });

    $scope.parseNnpData = function (data) {
        if (!data) {
            return [];
        }

        return data.replace('{', '').replace('}', '').split(',');
    };

    $scope.stringifyNnpData = function (data) {
        if (!data || data == '{}') {
            return '{}';
        }

        return '{' + data.join(',') + '}';
    };

    $scope.save = function () {
        var data = angular.copy($scope.item);

        data.nnp_country = $scope.stringifyNnpData(data.nnp_country);
        data.nnp_region = $scope.stringifyNnpData(data.nnp_region);
        data.nnp_city = $scope.stringifyNnpData(data.nnp_city);
        data.nnp_operator = $scope.stringifyNnpData(data.nnp_operator);
        data.nnp_ndc_type = $scope.stringifyNnpData(data.nnp_ndc_type);

        PricelistFilterA.save(data).then(function () {
            $modalInstance.close();
        });
    };

    $scope.setNnpFields = function(data) {
        try {
            $scope.item.nnp_ndc_type = $scope.parseNnpData($scope.item.nnp_ndc_type);

            if ($scope.item.nnp_filter != '') {
                $scope.nnpMode = $scope.NNP_MODE_FILTER;
            }

            if ($scope.item.nnp_country !== '{}') {
                $scope.item.nnp_country = $scope.parseNnpData($scope.item.nnp_country);

                if ($scope.item.nnp_country) {
                    Nnp.regionList({country_code: $scope.item.nnp_country}).then(function (data) {
                        regionLoadComplete = true;
                        $scope.regionList = data;
                        $scope.item.nnp_region = $scope.parseNnpData($scope.item.nnp_region);

                        if ($scope.item.nnp_region) {
                            $scope.item.nnp_city = $scope.parseNnpData($scope.item.nnp_city);
                            Nnp.cityList({
                                country_code: $scope.item.nnp_country,
                                region: $scope.item.nnp_region
                            }).then(function (data) {
                                $scope.cityList = data;
                            });
                        }
                    });

                    Nnp.operatorList({country_code: $scope.item.nnp_country}).then(function (data) {
                        $scope.operatorList = data;
                        $scope.item.nnp_operator = $scope.parseNnpData($scope.item.nnp_operator);
                    });
                }
            }
        } catch (error) {
            $scope.nnpDataParseError = true;
        }
    };

    $scope.back = function () {
        $modalInstance.close();
    };
};