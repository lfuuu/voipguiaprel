var PricelistFilterBEditCtrl = function($scope, $rootScope, $q, Major, PricelistFilterB, Nnp, List, params, $modalInstance, $window, Redirect) {

    $scope.NNP_MODE_FILTER = 1;
    $scope.NNP_MODE_PARAMETERS = 2;

    $scope.saveEnabled = false;
    $scope.errors = {
        error: false
    };

    var countryLoadComplete = false;
    var regionLoadComplete = false;
    var cityLoadComplete = false;
    var operatorLoadComplete = false;
    var ndcLoadComplete = false;

    $scope.round_type = [
        {id: 1, name: 'round'},
        {id: 2, name: 'ceil'}
    ];

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
            ndcLoadComplete = false;
            if (!newValue || newValue.length == 0) {
                $scope.item.nnp_region = null;
                $scope.item.nnp_city = null;
                $scope.item.nnp_operator = null;
                $scope.item.nnp_ndc_type = null;
                $scope.item.nnp_ndc = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.regionList = null;
                $scope.operatorList = null;
                $scope.ndcList = null;

                if (countryLoadComplete) {
                    var region = $scope.item.nnp_region;
                    var city = $scope.item.nnp_city;
                    var operator = $scope.item.nnp_operator;
                    var ndc = $scope.item.nnp_ndc;
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

                Nnp.ndcList({country_code: newValue}).then(function (data) {
                    $scope.ndcList = data;

                    if (countryLoadComplete) {
                        $scope.item.nnp_ndc = ndc;
                        ndcLoadComplete = true;
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

    var date = new Date();
    var pricelistDate = new Date(params.pricelist_date_start);

    if (params.id) {
        PricelistFilterB.get({id: params.id}).then(function (data) {
            $scope.item = data;

            $scope.item.prefixes_date_start = date > pricelistDate ? date.toISOString().slice(0, 10) : pricelistDate.toISOString().slice(0, 10);
            $scope.item.prefixes_date_end = '3000-01-01';
            $scope.item.prefixes_replace = false;

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
    } else if (params.filter_a_id) {
        $scope.item = {
            pricelist_filter_a_id: params.filter_a_id,
            nnp_destination: null,
            nnp_country: null,
            nnp_city: null,
            nnp_region: null,
            nnp_operator: null,
            nnp_ndc_type: null,
            nnp_ndc: null,
            mode_selected: true,
            interconnect_price: 0,
            ported_num_price: 0,
            operator_price: 0,
            transit_price: 0,
            tarification_free_seconds: params.pricelist_default_tarification_free_seconds,
            tarification_interval_seconds: params.pricelist_default_tarification_interval_seconds,
            tarification_type: params.pricelist_default_tarification_type,
            tarification_min_paid_seconds: params.pricelist_default_tarification_min_paid_seconds,
            filter_country: 643,
            rating: 1,
            use_for_minimum: false,
            use_cutoff_for_minimum: false,
            prefixes_date_start: date > pricelistDate ? date.toISOString().slice(0, 10) : pricelistDate.toISOString().slice(0, 10),
            prefixes_date_end: '3000-01-01',
            prefixes_replace: false
        };

        $scope.saveEnabled = true;

        $scope.$watch('item.nnp_country', watchers.nnp_country);
        $scope.$watch('item.nnp_region', watchers.nnp_region);
        $scope.$watch('item.filter_country', watchers.filter_country);
    } else {
        $scope.item = {
            nnp_destination: null,
            nnp_country: null,
            nnp_city: null,
            nnp_region: null,
            nnp_operator: null,
            nnp_ndc_type: null,
            nnp_ndc: null,
            mode_selected: true,
            interconnect_price: 0,
            ported_num_price: 0,
            operator_price: 0,
            transit_price: 0,
            tarification_free_seconds: 0,
            tarification_interval_seconds: 60,
            tarification_type: 2,
            tarification_min_paid_seconds: 0,
            filter_country: 643,
            rating: 1,
            use_for_minimum: false,
            use_cutoff_for_minimum: false,
            prefixes_date_start: date > pricelistDate ? date.toISOString().slice(0, 10) : pricelistDate.toISOString().slice(0, 10),
            prefixes_date_end: '3000-01-01',
            prefixes_replace: false
        };

        $scope.saveEnabled = true;

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
        $scope.errors = [];
        
        var data = angular.copy($scope.item);

        data.nnp_country = $scope.stringifyNnpData(data.nnp_country);
        data.nnp_region = $scope.stringifyNnpData(data.nnp_region);
        data.nnp_city = $scope.stringifyNnpData(data.nnp_city);
        data.nnp_operator = $scope.stringifyNnpData(data.nnp_operator);
        data.nnp_ndc_type = $scope.stringifyNnpData(data.nnp_ndc_type);
        data.nnp_ndc = $scope.stringifyNnpData(data.nnp_ndc);

        PricelistFilterB.save(data).then(function (result) {
            if (result.error) {
                $scope.displayError(result);
                return;
            } else {
                $modalInstance.close();
            }
        });
    };

    $scope.displayError = function(response)
    {
        $scope.errors[response.field + '_error'] = response.error;
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
                    var regionList = Nnp.regionList({country_code: $scope.item.nnp_country}).then(function (data) {
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

                    var operatorList = Nnp.operatorList({country_code: $scope.item.nnp_country}).then(function (data) {
                        $scope.operatorList = data;
                        $scope.item.nnp_operator = $scope.parseNnpData($scope.item.nnp_operator);
                    });

                    var ndcList = Nnp.ndcList({country_code: $scope.item.nnp_country}).then(function (data) {
                        $scope.ndcList = data;
                        $scope.item.nnp_ndc = $scope.parseNnpData($scope.item.nnp_ndc);
                    });

                    $q.all([regionList, operatorList, ndcList]).then(function () {
                        $scope.saveEnabled = true;
                    });
                } else {
                    $scope.saveEnabled = true;
                }
            } else {
                $scope.saveEnabled = true;
            }
        } catch (error) {
            $scope.nnpDataParseError = true;
        }
    };

    $scope.saveAndUpdate = function()
    {
        var data = angular.copy($scope.item);

        data.nnp_country = $scope.stringifyNnpData(data.nnp_country);
        data.nnp_region = $scope.stringifyNnpData(data.nnp_region);
        data.nnp_city = $scope.stringifyNnpData(data.nnp_city);
        data.nnp_operator = $scope.stringifyNnpData(data.nnp_operator);
        data.nnp_ndc_type = $scope.stringifyNnpData(data.nnp_ndc_type);
        data.nnp_ndc = $scope.stringifyNnpData(data.nnp_ndc);

        PricelistFilterB.saveAndUpdate(data).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.close();
    };
};