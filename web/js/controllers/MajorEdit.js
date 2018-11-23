var MajorEditCtrl = function($scope, Nnp, Major, params, $modalInstance, $window) {
    $scope.NNP_MODE_DIRECTION = 1;
    $scope.NNP_MODE_FILTER = 2;

    $scope.nnpMode = $scope.NNP_MODE_DIRECTION;

    var countryLoadComplete = false;
    var regionLoadComplete = false;
    var cityLoadComplete = false;
    var operatorLoadComplete = false;

    var watchers = {
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

    if (params.id) {
        Major.get({id: params.id}).then(function(data){
            $scope.item = data;

            $scope.setNnpFields(data);

            $scope.$watch('item.nnp_country', watchers.nnp_country);
            $scope.$watch('item.nnp_region', watchers.nnp_region);
        });
    } else {
        $scope.item = {
            nnp_destination: null,
            nnp_country: null,
            nnp_city: null,
            nnp_region: null,
            nnp_operator: null,
            nnp_ndc_type: null,
            nnp_exclude_destination: false,
            nnp_exclude_country: false,
            nnp_exclude_city: false,
            nnp_exclude_region: false,
            nnp_exclude_operator: false,
            nnp_exclude_ndc_type: false,
        };

        if (params.country_code) {
            $scope.item.country_code = params.country_code;
            $scope.item.order = params.count + 1;
        }

        $scope.$watch('item.nnp_country', watchers.nnp_country);
        $scope.$watch('item.nnp_region', watchers.nnp_region);

    }

    Nnp.destinationList().then(function (data) {
        $scope.destinationList = data;
    });

    Nnp.countryList().then(function (data) {
        $scope.countryList = data;
        countryLoadComplete = true;
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

            $scope.item.nnp_destination = filterData.nnp_destination_id;
            $scope.item.nnp_exclude_operator = filterData.exclude_operators;
            $scope.item.nnp_exclude_destination = filterData.exclude_destination;
            $scope.item.nnp_exclude_country = filterData.exclude_country;
            $scope.item.nnp_exclude_city = filterData.exclude_city;
            $scope.item.nnp_exclude_region = filterData.exclude_region;
            $scope.item.nnp_exclude_ndc_type = filterData.exclude_ndc_type;
            $scope.item.nnp_ndc_type = filterData.ndc_type_id;

            if (filterData.country_code) {
                $scope.nnpMode = $scope.NNP_MODE_FILTER;
            }

            $scope.item.nnp_country = filterData.country_code;

            if ($scope.item.nnp_country) {
                Nnp.regionList({country_code: $scope.item.nnp_country}).then(function (data) {
                    regionLoadComplete = true;
                    $scope.regionList = data;
                    $scope.item.nnp_region = filterData.region_id;

                    if ($scope.item.nnp_region) {
                        $scope.item.nnp_city = filterData.city_id;
                        Nnp.cityList({country_code: $scope.item.nnp_country, region: $scope.item.nnp_region}).then(function (data) {
                            $scope.cityList = data;
                            $scope.cities = data;

                        });
                    }
                });

                Nnp.operatorList({country_code: $scope.item.nnp_country}).then(function (data) {
                    $scope.operatorList = data;
                    $scope.item.nnp_operator = filterData.operator_id;
                });
            }
        } catch (error) {
            $scope.nnpDataParseError = true;
        }
    };

    $scope.save = function()
    {
        var data = angular.copy($scope.item);

        if ($scope.nnpMode == $scope.NNP_MODE_DIRECTION) {
            delete data.nnp_country;
            delete data.nnp_region;
            delete data.nnp_city;
            delete data.nnp_operator;
            delete data.nnp_ndc_type;
            delete data.nnp_exclude_country;
            delete data.nnp_exclude_region;
            delete data.nnp_exclude_city;
            delete data.nnp_exclude_operator;
            delete data.nnp_exclude_ndc_type;
        } else {
            delete data.nnp_destination;
            delete data.nnp_exclude_destination;
        }

        Major.save(data).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    };
};