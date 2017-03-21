var PrefixlistEditCtrl = function($scope, $rootScope, Prefixlist, Billing, Nnp, params, $modalInstance, $window, Redirect) {

    $scope.$watch('item.rossvyaz_country_id', function (newValue, oldValue) {
        if (newValue != oldValue) {
            $scope.item.rossvyaz_operator_id = null;
            $scope.item.rossvyaz_region_id = null;
            $scope.item.rossvyaz_city_id = null;

            $scope.cities = null;
        }
    });

    $scope.$watch('item.rossvyaz_region_id', function (newValue, oldValue) {
        if (newValue != oldValue) {
            $scope.item.rossvyaz_city_id = null;
            $scope.cities = null;
            Billing.cities($scope.item).then(function (data){
                $scope.cities = data;
            });
        }
    });

    $scope.$watch('item.nnp_country', function (newValue, oldValue) {
        if (newValue != oldValue) {
            $scope.regionList = null;
            $scope.operatorList = null;

            if (!newValue) {
                $scope.item.nnp_region = null;
                $scope.item.nnp_city = null;
                $scope.item.nnp_operator = null;
                $scope.item.nnp_ndc_type = null;
            }

            Nnp.regionList(newValue).then(function (data) {
                $scope.regionList = data;
            });

            Nnp.operatorList(newValue).then(function (data) {
                $scope.operatorList = data;
            });
        }
    });

    $scope.$watch('item.nnp_region', function (newValue, oldValue) {
        if (newValue != oldValue) {
            $scope.cityList = null;

            if (!newValue) {
                $scope.item.nnp_city = null;
            }

            Nnp.cityList($scope.item.nnp_country, newValue).then(function (data) {
                $scope.cityList = data;
            });
        }
    });

    $scope.$on('prefixlistUpdateCount', function(event, data) {
        if ($scope.item.id) {
            $scope.item.count = data;
        }
    });

    $scope.$on('prefixlistUpdateSuccess', function(event, data) {
        $modalInstance.dismiss();
    });

    $scope.nnp_parse_error = false;

    if (params.id) {
        Prefixlist.get({id: params.id}).then(function (data) {
            $scope.item = data;

            $scope.setType($scope.item.type_id);

            if ($scope.item.type_id == 1) {
                var manual_list = [];
                for (var i in $scope.item.manual_list) {
                    manual_list.push({prefix: $scope.item.manual_list[i]})
                }
                $scope.item.manual_list = manual_list;
            }

            if ($scope.item.type_id == 2) {
                var smezhnost_list = [];
                for (var i in $scope.item.smezhnost_list) {
                    smezhnost_list.push({network_type_id: $scope.item.smezhnost_list[i]})
                }
                $scope.item.smezhnost_list = smezhnost_list;
            }

            if ($scope.item.type_id == 3) {
                if ($scope.item.rossvyaz_region_id) {
                    Billing.cities($scope.item).then(function (data) {
                        $scope.cities = data;
                    });
                }
            }

            if ($scope.item.type_id == 6) {
                try {
                    var filterData = $.parseJSON($scope.item.nnp_filter_json);

                    $scope.item.nnp_destination = filterData.nnp_destination_id;
                    $scope.item.nnp_country = filterData.country_code;
                    $scope.item.nnp_region = filterData.region_id;
                    $scope.item.nnp_city = filterData.city_id;
                    $scope.item.nnp_operator = filterData.operator_id;
                    $scope.item.nnp_ndc_type = filterData.ndc_type_id;
                } catch (error) {
                    $scope.nnp_parse_error = true;
                    console.log(error);
                }
            }

            if ($scope.item.type_id == 4) {
                setTimeout(function () {
                    $('#upload-csv-file').fileapi({
                        url: '/prefixlist/upload-csv?id=' + $scope.item.id,
                        multiple: true,
                        maxSize: 20 * FileAPI.MB,
                        autoUpload: true,
                        elements: {
                            size: '.js-size',
                            active: { show: '.js-upload', hide: '.js-browse' },
                            progress: '.js-progress'
                        },
                        onComplete: function (e, result) {
                            $scope.item.count = result.result.data.count;
                            $('#upload-csv-file').hide();
                            $scope.$apply('item.id');
                        }
                    });
                }, 200);
            }
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id,
            manual_list: [],
            smezhnost_list: [],
            rossvyaz_operators: [],
            exclude_operators: false,
            nnp_destination: null,
            nnp_country: null,
            nnp_city: null,
            nnp_region: null,
            nnp_operator: null,
            nnp_ndc_type: null
        };
    }

    Billing.countries().then(function(data){
        $scope.countries = data;
    });

    Billing.regions().then(function(data){
        $scope.regions = data;
    });

    Billing.networkTypes().then(function(data){
        $scope.networkTypes = data;
    });

    Nnp.destinationList().then(function (data) {
        $scope.destinationList = data;
    });

    Nnp.countryList().then(function (data) {
        $scope.countryList = data;
    });

    Nnp.ndcTypeList().then(function (data) {
        $scope.ndcTypeList = data;
    });

    $scope.setType = function(type_id) {
        $scope.item.type_id = type_id;
    };

    $scope.addPrefix = function () {
        $scope.item.manual_list.unshift({prefix:''});
    };

    $scope.removePrefix = function (index) {
        $scope.item.manual_list.splice(index, 1);
    };

    $scope.addSmezhnost= function () {
        $scope.item.smezhnost_list.unshift({network_type_id:''});
    };

    $scope.removeSmezhnost = function (index) {
        $scope.item.smezhnost_list.splice(index, 1);
    };

    $scope.addOperator = function () {
        Redirect.selectRossvyazOperator().then(function (item) {
            $scope.item.rossvyaz_operators.push({
                id: item.id,
                name: item.name
            });
        });
    };

    $scope.removeOperator = function (index) {
        $scope.item.rossvyaz_operators.splice(index, 1);
    };

    $scope.save = function () {
        var data = angular.copy($scope.item);
        data.manual_list = [];
        data.smezhnost_list = [];

        if ($scope.item.type_id == 1) {
            for (var i in $scope.item.manual_list) {
                data.manual_list.push($scope.item.manual_list[i].prefix)
            }
        }

        if ($scope.item.type_id == 1) {
            for (var i in $scope.item.smezhnost_list) {
                data.smezhnost_list.push($scope.item.smezhnost_list[i].network_type_id)
            }
        }

        Prefixlist.save(data).then(function (result) {
            if (result && result.id && $scope.item.type_id == 6) {
                Redirect.prefixlistCalculate(result.id, $scope.item.name);
            } else {
                $modalInstance.close();
            }
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };

};