var PrefixlistEditCtrl = function($scope, $rootScope, Prefixlist, Billing, Nnp, Pbx, Server, params, $modalInstance, $window, Redirect) {

    var STATUS_SUCCESS = 'SUCCESS';
    var STATUS_ERROR = 'ERROR';

    $scope.TYPE_ID_MANUAL = 1;
    $scope.TYPE_ID_LOCAL = 2;
    $scope.TYPE_ID_ROSSVYAZ = 3;
    $scope.TYPE_ID_CSV = 4;
    $scope.TYPE_ID_EXPENSIVE = 5;
    $scope.TYPE_ID_NNP = 6;
    $scope.TYPE_ID_7800 = 7;
    $scope.TYPE_ID_DID_ON_VPBX = 8;
    $scope.TYPE_ID_FMC = 9;
    $scope.TYPE_ID_PARTED_NUM = 10;

    $scope.NNP_MODE_DIRECTION = 1;
    $scope.NNP_MODE_FILTER = 2;

    $scope.nnpMode = $scope.NNP_MODE_DIRECTION;

    var typeWithBuffer = [$scope.TYPE_ID_NNP, $scope.TYPE_ID_7800, $scope.TYPE_ID_DID_ON_VPBX, $scope.TYPE_ID_FMC, $scope.TYPE_ID_PARTED_NUM];

    var countryLoadComplete = false;
    var regionLoadComplete = false;
    var cityLoadComplete = false;
    var operatorLoadComplete = false;

    var watchers = {
        rossvyaz_country_id: function (newValue, oldValue) {
            if (newValue != oldValue) {
                $scope.item.rossvyaz_operator_id = null;
                $scope.item.rossvyaz_region_id = null;
                $scope.item.rossvyaz_city_id = null;

                $scope.cities = null;
            }
        },
        rossvyaz_region_id: function (newValue, oldValue) {
            if (newValue != oldValue) {
                $scope.item.rossvyaz_city_id = null;
                $scope.cities = null;
                Billing.cities($scope.item).then(function (data) {
                    $scope.cities = data;
                    $scope.cityList = data;
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

    if (params.id) {
        Prefixlist.get({id: params.id}).then(function (data) {
            $scope.item = data;

            if (typeWithBuffer.indexOf($scope.item.type_id) != -1) {
                $scope.hasBuffer = true;
            } else {
                $scope.hasBuffer = false;
            }

            Pbx.read($scope.item.servers).then(function (data) {
                $scope.pbxList = data;
            });

            $scope.setType($scope.item.type_id);
            $scope.item.count = $scope.item.prefixes;

            if ($scope.item.type_id == $scope.TYPE_ID_MANUAL) {
                var manual_list = [];
                for (var i in $scope.item.manual_list) {
                    manual_list.push({prefix: $scope.item.manual_list[i]})
                }
                $scope.item.manual_list = manual_list;
            }

            if ($scope.item.type_id == $scope.TYPE_ID_LOCAL) {
                var smezhnost_list = [];
                for (var i in $scope.item.smezhnost_list) {
                    smezhnost_list.push({network_type_id: $scope.item.smezhnost_list[i]})
                }
                $scope.item.smezhnost_list = smezhnost_list;
            }

            if ($scope.item.type_id == $scope.TYPE_ID_ROSSVYAZ) {
                if ($scope.item.rossvyaz_region_id) {
                    Billing.cities($scope.item).then(function (data) {
                        $scope.cities = data;
                        $scope.cityList = data;
                    });
                }
            }

            $scope.setNnpFields(data);

            if ($scope.item.type_id == $scope.TYPE_ID_CSV) {
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
                            // $('#upload-csv-file').hide();
                            $scope.$apply('item.id');
                        }
                    });
                }, 200);
            }

            $scope.$watch('item.rossvyaz_country_id', watchers.rossvyaz_country_id);
            $scope.$watch('item.rossvyaz_region_id', watchers.rossvyaz_region_id);

            $scope.$watch('item.nnp_country', watchers.nnp_country);
            $scope.$watch('item.nnp_region', watchers.nnp_region);
        });

        Prefixlist.findUsagesInNumbers({id: params.id}).then(function (data) {
            $scope.usagesInNumbers = data;
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
            nnp_exclude_operators: false,
            nnp_ndc_type: null,
            count: 0
        };

        $scope.$watch('item.rossvyaz_country_id', watchers.rossvyaz_country_id);
        $scope.$watch('item.rossvyaz_region_id', watchers.rossvyaz_region_id);

        $scope.$watch('item.nnp_country', watchers.nnp_country);
        $scope.$watch('item.nnp_region', watchers.nnp_region);
    }

    Billing.countries().then(function(data){
        $scope.countries = data;
    });

    Billing.regions().then(function(data){
        $scope.regions = data;
        $scope.regionList = data;
    });

    Billing.networkTypes().then(function(data){
        $scope.networkTypes = data;
    });

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

    Server.list().then(function (data) {
        $scope.serverList = data;
    });

    $scope.setType = function(type_id) {
        $scope.item.type_id = type_id;

        if (typeWithBuffer.indexOf($scope.item.type_id) != -1) {
            $scope.hasBuffer = true;
        } else {
            $scope.hasBuffer = false;
        }
    };

    $scope.setNnpMode = function(nnpMode) {
        $scope.nnpMode = nnpMode;
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

    $scope.prefixlistNnpCalculate = function (id) {
        $scope.nnpProcessed = true;
        $scope.nnpProcessComplete = false;
        $scope.nnpProcessFailed = false;

        Prefixlist.nnpCalculation(id).then(function (data) {
            if (data.response == STATUS_ERROR) {
                $scope.nnpProcessFailed = data.message;
                return false;
            }

            if (data.message.status != STATUS_SUCCESS) {
                $scope.nnpProcessFailed = data.message.message;
                return false;
            }

            $scope.nnpProcessed = false;
            $scope.nnpProcessComplete = true;
            $scope.item.count = data.message.prefix_list_size;
            $modalInstance.close();
        });
    };

    $scope.addServer = function() {
        if (!$scope.item.servers) {
            $scope.item.servers = [];
        }

        $scope.item.servers.push({id: null});
    };

    $scope.removeServer = function(index) {
        if (!$scope.item.servers) {
            $scope.item.servers = [];
        } else {
            $scope.item.servers.splice(index, 1);
        }

        Pbx.read($scope.item.servers).then(function (data) {
            $scope.pbxList = data;
        });
    };

    $scope.serverSelected = function() {
        Pbx.read($scope.item.servers).then(function (data) {
            $scope.pbxList = data;
        });
    }

    $scope.addPbx = function() {
        if (!$scope.item.pbx_list) {
            $scope.item.pbx_list = [];
        }
        $scope.item.pbx_list.push({id: null});
    };

    $scope.removePbx = function(index) {
        if (!$scope.item.pbx_list) {
            $scope.item.pbx_list = [];
        } else {
            $scope.item.pbx_list.splice(index, 1);
        }
    };

    $scope.save = function () {
        var data = angular.copy($scope.item);
        data.manual_list = [];
        data.smezhnost_list = [];

        if ($scope.item.type_id == $scope.TYPE_ID_MANUAL) {
            for (var i in $scope.item.manual_list) {
                data.manual_list.push($scope.item.manual_list[i].prefix)
            }
        }

        if ($scope.item.type_id == $scope.TYPE_ID_LOCAL) {
            for (var i in $scope.item.smezhnost_list) {
                data.smezhnost_list.push($scope.item.smezhnost_list[i].network_type_id)
            }
        }

        if ($scope.item.type_id == $scope.TYPE_ID_NNP) {
            if ($scope.nnpMode == $scope.NNP_MODE_DIRECTION) {
                delete data.nnp_country;
                delete data.nnp_region;
                delete data.nnp_city;
                delete data.nnp_operator;
                delete data.nnp_is_exclude_operators;
                delete data.nnp_ndc_type;
            } else {
                delete data.nnp_destination;
            }
        }

        Prefixlist.save(data).then(function (result) {
            // if (result && result.id && $scope.item.id && ($scope.item.type_id == 6 || $scope.item.type_id == 7 || $scope.item.type_id == 8)) {
            //     Prefixlist.generatePrefixlist($scope.item.id, $scope.item.type_id).then(function (data) {
            //         $modalInstance.close();
            //     });
            // } else {
                $modalInstance.close();
            // }
        });
    };

    $scope.applyPrefixlistBuffer = function (id) {
        if (!$window.confirm('Применить буфер?')) return;

        Prefixlist.applyBuffer(id).then(function (data) {
            if (data.response == STATUS_ERROR) {
                $scope.apply_buffer_success = false;
                $scope.apply_buffer_error = true;
            } else {
                $scope.apply_buffer_success = true;
                $scope.apply_buffer_error = false;

                Prefixlist.get({id: id}).then(function (data) {
                    $scope.item = data;

                    var filterData = $.parseJSON($scope.item.nnp_filter_json);

                    if (filterData.country_code) {
                        $scope.nnpMode = $scope.NNP_MODE_FILTER;
                    }

                    $scope.item.nnp_destination = filterData.nnp_destination_id;
                    $scope.item.nnp_is_exclude_operators = filterData.is_exclude_operators;
                    $scope.item.nnp_ndc_type = filterData.ndc_type_id;
                    $scope.item.nnp_country = filterData.country_code;
                    $scope.item.nnp_region = filterData.region_id;
                    $scope.item.nnp_operator = filterData.operator_id;
                    $scope.item.nnp_city = filterData.city_id;
                });
            }
        });
    }

    $scope.generatePrefixlist = function (id, type) {
        Prefixlist.generatePrefixlist(id, type).then(function (data) {
            if (data.response == STATUS_ERROR) {
                $scope.generate_success = false;
                $scope.generate_error = true;
            } else {
                $scope.generate_success = true;
                $scope.generate_error = false;

                Prefixlist.get({id: id}).then(function (data) {
                    $scope.item = data;

                    var filterData = $.parseJSON($scope.item.nnp_filter_json);

                    if (filterData.country_code) {
                        $scope.nnpMode = $scope.NNP_MODE_FILTER;
                    }

                    $scope.item.nnp_destination = filterData.nnp_destination_id;
                    $scope.item.nnp_is_exclude_operators = filterData.is_exclude_operators;
                    $scope.item.nnp_ndc_type = filterData.ndc_type_id;
                    $scope.item.nnp_country = filterData.country_code;
                    $scope.item.nnp_region = filterData.region_id;
                    $scope.item.nnp_operator = filterData.operator_id;
                    $scope.item.nnp_city = filterData.city_id;
                });
            }
        });
    }

    $scope.setNnpFields = function(data) {
        if ($scope.item.type_id == $scope.TYPE_ID_NNP) {
            try {
                var filterData = $.parseJSON($scope.item.nnp_filter_json);

                $scope.item.nnp_destination = filterData.nnp_destination_id;
                $scope.item.nnp_is_exclude_operators = filterData.is_exclude_operators;
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

        }
    }

    $scope.back = function () {
        $modalInstance.close();
    };

    $scope.clickNumberItem = function(item) {
        if (window.getSelection().type == 'Range') return;

        Redirect.numberEdit(item.id).then(function () {
            $scope.init();
        });
    }

};