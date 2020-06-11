var MajorEditCtrl = function($scope, $q, Redirect, Nnp, Major, List, params, $modalInstance, $window) {

    $scope.saveEnabled = false;

    var watchers = {
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

    if (params.id) {
        Major.get({id: params.id}).then(function(data){
            $scope.item = data;

            $scope.setNnpFields();

            Major.findUsagesInPricelists({id: params.id}).then(function (data) {
                $scope.usagesInPricelists = data;
            });

            $scope.$watch('item.nnp_region', watchers.nnp_region);
        });
    } else {
        $scope.item = {
            nnp_country: null,
            nnp_city: null,
            nnp_region: null,
            nnp_operator: null,
            nnp_ndc_type: null,
            nnp_ndc: null,
            nnp_exclude_country: false,
            nnp_exclude_city: false,
            nnp_exclude_region: false,
            nnp_exclude_operator: false,
            nnp_exclude_ndc_type: false,
            nnp_exclude_ndc: false
        };

        if (params.country_code) {
            $scope.item.country_code = params.country_code;
            $scope.item.order = params.count + 1;
        }

        $scope.saveEnabled = true;

        $scope.$watch('item.nnp_region', watchers.nnp_region);

    }

    Nnp.countryList().then(function (data) {
        $scope.countryList = data;
        countryLoadComplete = true;
        $scope.$watch('item.nnp_country', watchers.nnp_country);
    });

    Nnp.ndcTypeList().then(function (data) {
        $scope.ndcTypeList = data;
    });

    $scope.setNnpMode = function(nnpMode) {
        $scope.nnpMode = nnpMode;
    };

    $scope.setNnpFields = function(data) {
        try {
            var filterData = $.parseJSON($scope.item.nnp_filter_json);

            $scope.item.nnp_exclude_operator = filterData.exclude_operators;
            $scope.item.nnp_exclude_country = filterData.exclude_country;
            $scope.item.nnp_exclude_city = filterData.exclude_city;
            $scope.item.nnp_exclude_region = filterData.exclude_region;
            $scope.item.nnp_exclude_ndc_type = filterData.exclude_ndc_type;
            $scope.item.nnp_exclude_ndc = filterData.exclude_ndc;
            $scope.item.nnp_ndc_type = filterData.ndc_type_id;

            if (filterData.country_code) {
                $scope.nnpMode = $scope.NNP_MODE_FILTER;
            }

            $scope.item.nnp_country = filterData.country_code;
            $scope.item.nnp_region = filterData.region_id;
            $scope.item.nnp_operator = filterData.operator_id;
            $scope.item.nnp_city = filterData.city_id;
            $scope.item.nnp_ndc = filterData.ndc;
            
            $scope.saveEnabled = true;
        } catch (error) {
            $scope.nnpDataParseError = true;
        }
    };

    List.majorGroup().then(function (data) {
        $scope.majorGroupList = data;
    });

    $scope.clickPricelistItem = function(item) {
        if (window.getSelection().type == 'Range') return;

        Redirect.pricelistShortView(item.id, item.service_type_id).then(function () {
            //do_nothing
        });
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

    $scope.save = function()
    {
        var data = angular.copy($scope.item);

        Major.save(data).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.saveAndUpdate = function()
    {
        var data = angular.copy($scope.item);

        Major.saveAndUpdate(data).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    };
};