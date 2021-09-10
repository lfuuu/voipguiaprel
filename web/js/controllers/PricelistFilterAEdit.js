var PricelistFilterAEditCtrl = function($scope, $rootScope, $q, Major, PricelistFilterA, Nnp, List, params, $modalInstance, $window, Redirect, PricelistFilterBHistory, AlphaNumber) {

    $scope.NNP_MODE_FILTER = 1;
    $scope.NNP_MODE_PARAMETERS = 2;

    $scope.saveEnabled = false;
    
    $scope.errors = {
        error: false
    };

    $scope.loading = false;
    $scope.saveError = false;

    var watchers = {
        filter_country: function (newValue, oldValue) {
            if (newValue !== oldValue) {
                Major.read({country_code: newValue}).then(function (result) {
                    $scope.filterList = result;
                });
            }
        },
        nnp_country: function (newValue, oldValue) {
            if ((typeof $scope.item) == 'undefined') {
                return;
            }
            
            if (!newValue || newValue.length == 0 || (oldValue && newValue.length < oldValue.length)) {
                $scope.item.nnp_region = null;
                $scope.item.nnp_city = null;
                $scope.item.nnp_operator = null;
                $scope.item.nnp_ndc_type = null;
                $scope.item.nnp_ndc = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.regionList = null;
                $scope.cityList = null;
                $scope.operatorList = null;
                $scope.ndcList = null;
            }
        },
        nnp_region: function (newValue, oldValue) {
            if ((typeof $scope.item) == 'undefined') {
                return;
            }
            
            if (!newValue || newValue.length == 0 || (oldValue && newValue.length < oldValue.length)) {
                $scope.item.nnp_city = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.cityList = null;
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
            $scope.filter_b_replace = false;
            $scope.setNnpFields();
            $scope.setAlphaNumbers();

            if (data.nnp_filter && data.filter_country) {
                Major.read({country_code: data.filter_country}).then(function (result) {
                    $scope.filterList = result;
                });
            }
            $scope.saveEnabled = true;

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
            nnp_ndc: null,
            a2p_alphanumber: null,
            mode_selected: true,
            filter_b_replace: false,
            filter_country: 643
        };

        $scope.saveEnabled = true;

        $scope.$watch('item.nnp_region', watchers.nnp_region);
        $scope.$watch('item.filter_country', watchers.filter_country);
    } else {
        $scope.item = {
            nnp_country: null,
            nnp_city: null,
            nnp_region: null,
            nnp_operator: null,
            nnp_ndc_type: null,
            nnp_ndc: null,
            a2p_alphanumber: null,
            mode_selected: true,
            filter_b_replace: false,
            filter_country: 643
        };

        $scope.saveEnabled = true;

        $scope.$watch('item.nnp_region', watchers.nnp_region);
        $scope.$watch('item.filter_country', watchers.filter_country);
    }

    $scope.setNnpMode = function(nnpMode) {
        $scope.nnpMode = nnpMode;
    };

    Nnp.countryList().then(function (data) {
        $scope.countryList = data;
        countryLoadComplete = true;
        $scope.$watch('item.nnp_country', watchers.nnp_country);
    });

    AlphaNumber.alphaNumList().then(function (data) {
        $scope.alphaNumList = data;
    });

    Nnp.ndcTypeList().then(function (data) {
        $scope.ndcTypeList = data;
    });

    $scope.parseNnpData = function (data) {
        if (!data) {
            return [];
        }
        
        if ((typeof data) != 'string') {
            return data;
        }

        return data.replace('{', '').replace('}', '').split(',');
    };

    $scope.stringifyNnpData = function (data) {
        if (!data || data == '{}') {
            return '{}';
        }

        if (((typeof data) == 'string') && data.charAt(0) == '{') {
            return data;
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
        data.nnp_ndc = $scope.stringifyNnpData(data.nnp_ndc);
        data.a2p_alphanumber = $scope.stringifyNnpData(data.a2p_alphanumber);
        
        PricelistFilterA.save(data).then(
            function (result) {
                $scope.loading = false;
                if (result.error) {
                    $scope.displayError(result);
                    return;
                } else {
                    $modalInstance.close();
                }
            }
        ).catch(
            function (error) {
                $scope.loading = false;
                $scope.saveError = true;
            }
        );
    };
    
    $scope.displayError = function(response)
    {
        $scope.errors[response.field + '_error'] = response.error;
    };

    $scope.setAlphaNumbers = function() {
        if ($scope.item.a2p_alphanumber !== '{}') {
            $scope.item.a2p_alphanumber = $scope.parseNnpData($scope.item.a2p_alphanumber);
        }
    }

    $scope.setNnpFields = function() {
        try {
            $scope.item.nnp_ndc_type = $scope.parseNnpData($scope.item.nnp_ndc_type);

            if ($scope.item.nnp_filter != '') {
                $scope.nnpMode = $scope.NNP_MODE_FILTER;
            }

            if ($scope.item.nnp_country !== '{}') {
                $scope.item.nnp_country = $scope.parseNnpData($scope.item.nnp_country);
                $scope.item.nnp_region = $scope.parseNnpData($scope.item.nnp_region);
                $scope.item.nnp_operator = $scope.parseNnpData($scope.item.nnp_operator);
                $scope.item.nnp_city = $scope.parseNnpData($scope.item.nnp_city);
                $scope.item.nnp_ndc = $scope.parseNnpData($scope.item.nnp_ndc);
            }
        } catch (error) {
            $scope.nnpDataParseError = true;
        }
    };
    
    $scope.loadRegions = function () {
        if ($scope.item.nnp_country && $scope.item.nnp_country.length > 0) {
            Nnp.regionList({country_code: $scope.item.nnp_country}).then(function (data) {
                $scope.regionList = data;
                $scope.item.nnp_region = $scope.parseNnpData($scope.item.nnp_region);
            });
        }
    };
    
    $scope.loadOperators = function () {
        if ($scope.item.nnp_country && $scope.item.nnp_country.length > 0) {
            Nnp.operatorList({country_code: $scope.item.nnp_country}).then(function (data) {
                $scope.operatorList = data;
                $scope.item.nnp_operator = $scope.parseNnpData($scope.item.nnp_operator);
            });
        }
    };
    
    $scope.loadCities = function () {
        if ($scope.item.nnp_region && $scope.item.nnp_region.length > 0) {
            Nnp.cityList({
                country_code: $scope.item.nnp_country,
                region: $scope.item.nnp_region
            }).then(function (data) {
                $scope.cityList = data;
                $scope.item.nnp_city = $scope.parseNnpData($scope.item.nnp_city);
            });
        }
    };
    
    $scope.loadNdcs = function () {
        if ($scope.item.nnp_country && $scope.item.nnp_country.length > 0) {
            Nnp.ndcList({country_code: $scope.item.nnp_country}).then(function (data) {
                $scope.ndcList = data;
                $scope.item.nnp_ndc = $scope.parseNnpData($scope.item.nnp_ndc);
            });
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

        PricelistFilterA.saveAndUpdate(data).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.openFilterBHistory = function(item) {
        Redirect.pricelistFilterBHistoryView(item.id).then(function () {
            $scope.init();
        });
    }

    $scope.undoFilterBImport = function(item) {
        if (!$window.confirm("Вы уверены, что хотите отменить эту операцию?\n" + 
         "Будут отменены ВСЕ действия, произведенные с фильтрами Б\n" +
         "и относящимся к ним префиксам!")) return;

        PricelistFilterBHistory.undo(item);
        $modalInstance.close();
    }

    $scope.back = function () {
        $modalInstance.close();
    };
};