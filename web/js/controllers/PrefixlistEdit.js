var PrefixlistEditCtrl = function ($scope, $rootScope, $q, Prefixlist, Billing, Nnp, Pbx, List, Server, params, $modalInstance, $window, Redirect, Mnc) {

    var STATUS_SUCCESS = 'SUCCESS';
    var STATUS_ERROR = 'ERROR';

    $scope.TYPE_ID_MANUAL = 1;
    $scope.TYPE_ID_CSV = 4;
    $scope.TYPE_ID_NNP = 6;
    $scope.TYPE_ID_7800 = 7;
    $scope.TYPE_ID_DID_ON_VPBX = 8;
    $scope.TYPE_ID_FMC = 9;
    $scope.TYPE_ID_PARTED_NUM = 10;
    $scope.TYPE_ID_ROAMING = 11;
    $scope.TYPE_ID_VOIP_REGISTRY = 12;
    $scope.TYPE_ID_VOIP_NUMBER = 13;
    $scope.TYPE_ID_GT = 14;
    $scope.TYPE_ID_RN = 15;

    $scope.NNP_MODE_DIRECTION = 1;
    $scope.NNP_MODE_FILTER = 2;

    $scope.saveEnabled = false;
    $scope.nnpMode = $scope.NNP_MODE_DIRECTION;

    var typeWithBuffer = [$scope.TYPE_ID_NNP, $scope.TYPE_ID_7800, $scope.TYPE_ID_DID_ON_VPBX, $scope.TYPE_ID_FMC,
        $scope.TYPE_ID_PARTED_NUM, $scope.TYPE_ID_ROAMING, $scope.TYPE_ID_VOIP_REGISTRY, $scope.TYPE_ID_VOIP_NUMBER,
        $scope.TYPE_ID_GT, $scope.TYPE_ID_RN];

    var countryLoadComplete = false;
    var regionLoadComplete = false;
    var cityLoadComplete = false;
    var operatorLoadComplete = false;
    var ndcLoadComplete = false;
    var gtRegionLoadComplete = false;
    var gtOperatorLoadComplete = false;
    var rnRegionLoadComplete = false;
    var rnOperatorLoadComplete = false;
    var rnRouteMncLoadComplete = false;

    var countryLoadCompleteV2 = false;
    var cityLoadCompleteV2 = false;
    var regionLoadCompleteV2 = false;

    var watchers = {
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
        gt_country: function (newValue, oldValue) {
            gtRegionLoadComplete = false;
            gtOperatorLoadComplete = false;
            if (!newValue || newValue.length == 0) {
                $scope.item.gt_region = null;
                $scope.item.gt_operator = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.gtRegionList = null;

                if (countryLoadComplete) {
                    var region = $scope.item.gt_region;
                    var operator = $scope.item.gt_operator;
                }

                Nnp.regionList({country_code: newValue}).then(function (data) {
                    $scope.gtRegionList = data;

                    if (countryLoadComplete) {
                        $scope.item.gt_region = region;
                        gtRegionLoadComplete = true;
                    }
                });

                Nnp.operatorList({country_code: newValue}).then(function (data) {
                    $scope.gtOperatorList = data;

                    if (countryLoadComplete) {
                        $scope.item.gt_operator = operator;
                        gtOperatorLoadComplete = true;
                    }
                });
            }
        },
        registry_country: function (newValue, oldValue) {
            cityLoadComplete = false;
            if (!newValue || newValue.length == 0) {
                $scope.item.registry_city = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.cityList = null;

                if (countryLoadComplete) {
                    var city = $scope.item.registry_city;
                }

                Nnp.cityList({country_code: newValue}).then(function (data) {
                    $scope.cityList = data;
                    $scope.cities = data;

                    $scope.item.registry_city = city;
                    cityLoadComplete = true;
                });
            }
        },
        number_country: function (newValue, oldValue) {
            regionLoadComplete = false;
            if (!newValue || newValue.length == 0) {
                $scope.item.number_region = null;
                $scope.item.number_city = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.regionList = null;

                if (countryLoadComplete) {
                    var region = $scope.item.number_region;
                    var city = $scope.item.number_city;
                }

                Nnp.regionList({country_code: newValue}).then(function (data) {
                    $scope.regionList = data;

                    if (countryLoadComplete) {
                        $scope.item.number_region = region;
                        $scope.item.number_city = city;
                        regionLoadComplete = true;
                    }
                });
            }
        },
        number_country_v2: function (newValue, oldValue) {
            regionLoadCompleteV2 = false;
            if (!newValue || newValue.length == 0) {
                $scope.item.number_regions = null;
                $scope.item.number_cities = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.regionListV2 = null;

                if (countryLoadCompleteV2) {
                    var regions = $scope.item.number_regions;
                    var cities = $scope.item.number_cities;
                }

                Nnp.regionList({country_code: newValue}).then(function (data) {
                    $scope.regionListV2 = data;

                    if (countryLoadCompleteV2) {
                        $scope.item.number_regions = regions;
                        $scope.item.number_cities = cities;
                        regionLoadCompleteV2 = true;

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
        },
        number_region: function (newValue, oldValue) {
            cityLoadComplete = false;
            if (!newValue || newValue.length == 0) {
                $scope.item.number_city = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.cityList = null;
                var city = $scope.item.number_city;

                Nnp.cityList({country_code: $scope.item.number_country, region: newValue}).then(function (data) {
                    $scope.cityList = data;
                    $scope.cities = data;
                    $scope.item.number_city = city;
                    cityLoadComplete = true;
                });
            }
        },
        number_region_v2: function (newValue, oldValue) {
            cityLoadCompleteV2 = false;
            if (!newValue || newValue.length == 0) {
                $scope.item.number_cities = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.cityListV2 = null;

                var cities = $scope.item.number_cities;

                Nnp.cityList({country_code: $scope.item.number_countries, region: newValue}).then(function (data) {
                    $scope.cityListV2 = data;
                    $scope.cities = data;

                    $scope.item.number_cities = cities;
                    cityLoadCompleteV2 = true;

                });
            }
        },
        rn_country: function (newValue, oldValue) {
            rnRegionLoadComplete = false;
            rnOperatorLoadComplete = false;
            if (!newValue || newValue.length == 0) {
                $scope.item.rn_region = null;
                $scope.item.rn_region_fz = null;
                $scope.item.rn_operator = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.rnRegionList = null;
                if (countryLoadComplete) {
                    var region = $scope.item.rn_region;
                    var region_fz = $scope.item.rn_region_fz;
                    var operator = $scope.item.rn_operator;
                }

                Nnp.regionList({country_code: newValue}).then(function (data) {
                    $scope.rnRegionList = data;
                    if (countryLoadComplete) {
                        $scope.item.rn_region = region;
                        $scope.item.rn_region_fz = region_fz;
                        rnRegionLoadComplete = true;
                    }
                });

                Nnp.operatorList({country_code: newValue}).then(function (data) {
                    $scope.rnOperatorList = data;

                    if (countryLoadComplete) {
                        $scope.item.rn_operator = operator;
                        rnOperatorLoadComplete = true;
                    }
                });
            }
        },
    };

    $scope.nnpDataParseError = false;
    $scope.nnpProcessed = false;
    $scope.nnpProcessFailed = false;
    $scope.nnpProcessComplete = false;

    if (params.id) {
        Prefixlist.get({id: params.id}).then(function (data) {
            $scope.item = data;
            $scope.initialServerId = $scope.item.server_id;
            
            if (typeWithBuffer.indexOf($scope.item.type_id) != -1) {
                $scope.hasBuffer = true;
            } else {
                $scope.hasBuffer = false;
            }

            $scope.serverSelected();

            $scope.setType($scope.item.type_id);
            $scope.item.count = $scope.item.prefixes;

            if ($scope.item.type_id == $scope.TYPE_ID_MANUAL) {
                var manual_list = [];
                for (var i in $scope.item.manual_list) {
                    manual_list.push({prefix: $scope.item.manual_list[i]});
                }
                $scope.item.manual_list = manual_list;
                $scope.saveEnabled = true;
            }

            if ($scope.item.type_id == $scope.TYPE_ID_7800
                || $scope.item.type_id == $scope.TYPE_ID_DID_ON_VPBX
                || $scope.item.type_id == $scope.TYPE_ID_FMC
                || $scope.item.type_id == $scope.TYPE_ID_PARTED_NUM
                || $scope.item.type_id == $scope.TYPE_ID_ROAMING) {
                $scope.saveEnabled = true;
            }

            $scope.setNnpFields(data);
            $scope.setVoipRegistryFields(data);
            $scope.setVoipNumberFields(data);
            $scope.setVoipNumberFieldsV2(data);
            $scope.setGtFields(data);
            $scope.setRnFields(data);

            if ($scope.item.type_id == $scope.TYPE_ID_CSV) {
                setTimeout(function () {
                    $('#upload-csv-file').fileapi({
                        url: '/prefixlist/upload-csv?id=' + $scope.item.id,
                        multiple: true,
                        maxSize: 20 * FileAPI.MB,
                        autoUpload: true,
                        elements: {
                            size: '.js-size',
                            active: {show: '.js-upload', hide: '.js-browse'},
                            progress: '.js-progress'
                        },
                        onComplete: function (e, result) {
                            $scope.item.count = result.result.data.count;
                            $scope.$apply('item.id');
                        }
                    });
                }, 200);
                $scope.saveEnabled = true;
            }

            $scope.$watch('item.nnp_country', watchers.nnp_country);
            $scope.$watch('item.nnp_region', watchers.nnp_region);
            $scope.$watch('item.registry_country', watchers.registry_country);
            $scope.$watch('item.number_country', watchers.number_country);
            $scope.$watch('item.number_region', watchers.number_region);
            $scope.$watch('item.number_countries', watchers.number_country_v2);
            $scope.$watch('item.number_regions', watchers.number_region_v2);
            $scope.$watch('item.gt_country', watchers.gt_country);
            $scope.$watch('item.rn_country', watchers.rn_country);
        });

        Prefixlist.findUsagesInNumbers({id: params.id}).then(function (data) {
            $scope.usagesInNumbers = data;
        });

        Prefixlist.findUsagesInTrunkABRules({id: params.id}).then(function (data) {
            $scope.usagesInTrunkABRules = data;
        });
    } else {
        $scope.item = {
            server_id: ($scope.isCamel ? $scope.server.default_routing_server_id : $scope.server.id),
            manual_list: [],
            smezhnost_list: [],
            exclude_operators: false,
            nnp_destination: null,
            nnp_country: null,
            nnp_city: null,
            nnp_region: null,
            nnp_operator: null,
            nnp_is_exclude_operators: false,
            nnp_ndc_type: null,
            nnp_ndc: null,
            nnp_is_default: true,
            nnp_use_nnp_ported: false,
            nnp_is_exclude_destination: false,
            nnp_is_exclude_country: false,
            nnp_is_exclude_city: false,
            nnp_is_exclude_region: false,
            nnp_is_exclude_ndc_type: false,
            nnp_is_exclude_ndc: false,
            number_countries: null,
            number_regions: null,
            number_cities: null,
            number_ndc_types: null,
            number_sources: null,
            number_statuses: null,
            number_exclude_countries: false,
            number_exclude_regions: false,
            number_exclude_cities: false,
            number_exclude_ndc_types: false,
            number_exclude_sources: false,
            number_exclude_statuses: false,
            rn_region: null,
            rn_region_fz: null,
            rn_country: null,
            rn_operator: null,
            rn_use_nnp_ported: false,
            rn_route_mnc: null,
            rn_is_exclude_country: false,
            rn_is_exclude_operators: false,
            rn_is_exclude_region: false,
            rn_is_exclude_mnc: false,
            count: 0,
            sw_share_with_camel: ($scope.isCamel ? true : false)
        };
        $scope.initialServerId = $scope.item.server_id;
        if ($scope.isCamel) {
            $scope.item.type_id = $scope.TYPE_ID_GT;
        }

        $scope.saveEnabled = true;

        $scope.$watch('item.nnp_country', watchers.nnp_country);
        $scope.$watch('item.nnp_region', watchers.nnp_region);
        $scope.$watch('item.registry_country', watchers.registry_country);
        $scope.$watch('item.number_country', watchers.number_country);
        $scope.$watch('item.number_region', watchers.number_region);
        $scope.$watch('item.number_countries', watchers.number_country_v2);
        $scope.$watch('item.number_regions', watchers.number_region_v2);
        $scope.$watch('item.gt_country', watchers.gt_country);
        $scope.$watch('item.rn_country', watchers.rn_country);
    }

    Billing.countries().then(function (data) {
        $scope.countries = data;
    });

    Billing.regions().then(function (data) {
        $scope.regions = data;
        $scope.regionList = data;
    });

    Billing.networkTypes().then(function (data) {
        $scope.networkTypes = data;
    });

    Nnp.destinationList().then(function (data) {
        $scope.destinationList = data;
    });

    Nnp.countryList().then(function (data) {
        $scope.countryList = data;
        countryLoadComplete = true;
    });

    Nnp.countryList().then(function (data) {
        $scope.countryListV2 = data;
        countryLoadCompleteV2 = true;
    });

    Nnp.ndcTypeList().then(function (data) {
        $scope.ndcTypeList = data;
    });

    Nnp.sourceList().then(function (data) {
        $scope.sourceList = data;
    });

    Nnp.numberSourceList().then(function (data) {
        $scope.numberSourceList = data;
    });

    Nnp.numberStatusList().then(function (data) {
        $scope.numberStatusList = data;
    });

    Nnp.geoCityList().then(function (data) {
        $scope.geoCityList = data;
    });

    Nnp.geoCountryList().then(function (data) {
        $scope.geoCountryList = data;
    });

    Nnp.routeMncList().then(function (data) {
        $scope.rnRouteMncList = data;
    });

    Server.list().then(function (data) {
        $scope.serverList = data;
    });

    $scope.setType = function (type_id) {
        $scope.item.type_id = type_id;

        if (typeWithBuffer.indexOf($scope.item.type_id) != -1) {
            $scope.hasBuffer = true;
        } else {
            $scope.hasBuffer = false;
        }
    };

    $scope.setNnpMode = function (nnpMode) {
        $scope.nnpMode = nnpMode;
    };

    $scope.addPrefix = function () {
        $scope.item.manual_list.unshift({prefix: ''});
    };

    $scope.removePrefix = function (index) {
        $scope.item.manual_list.splice(index, 1);
    };

    $scope.addSmezhnost = function () {
        $scope.item.smezhnost_list.unshift({network_type_id: ''});
    };

    $scope.removeSmezhnost = function (index) {
        $scope.item.smezhnost_list.splice(index, 1);
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

    $scope.addServer = function () {
        if (!$scope.item.servers) {
            $scope.item.servers = [];
        }

        $scope.item.servers.push({id: null});
    };

    $scope.removeServer = function (index) {
        if (!$scope.item.servers) {
            $scope.item.servers = [];
        } else {
            $scope.item.servers.splice(index, 1);
        }

        Pbx.read($scope.item.servers).then(function (data) {
            $scope.pbxList = data;
        });
    };

    $scope.serverSelected = function () {
        Pbx.read($scope.item.servers).then(function (data) {
            $scope.pbxList = data;
        });

        List.trunkRoaming($scope.item.servers).then(function (data) {
            $scope.trunkRoamingList = data;
        });
    };

    $scope.addPbx = function () {
        if (!$scope.item.pbx_list) {
            $scope.item.pbx_list = [];
        }
        $scope.item.pbx_list.push({id: null});
    };

    $scope.removePbx = function (index) {
        if (!$scope.item.pbx_list) {
            $scope.item.pbx_list = [];
        } else {
            $scope.item.pbx_list.splice(index, 1);
        }
    };

    $scope.addTrunkRoaming = function () {
        if (!$scope.item.trunk_roaming_list) {
            $scope.item.trunk_roaming_list = [];
        }
        $scope.item.trunk_roaming_list.push({id: null, type: 'any'});
    };

    $scope.removeTrunkRoaming = function (index) {
        if (!$scope.item.trunk_roaming_list) {
            $scope.item.trunk_roaming_list = [];
        } else {
            $scope.item.trunk_roaming_list.splice(index, 1);
        }
    };

    $scope.save = function () {
        if ($scope.initialServerId !== $scope.server.id) {
            alert("Изменения нельзя сохранить, так как вы пытаетесь изменить список префиксов, который находится на другом регионе.");
            return;
        }

        var data = angular.copy($scope.item);
        data.manual_list = [];
        data.smezhnost_list = [];

        if ($scope.item.type_id == $scope.TYPE_ID_MANUAL) {
            for (var i in $scope.item.manual_list) {
                data.manual_list.push($scope.item.manual_list[i].prefix);
            }
        }

        if ($scope.item.type_id == $scope.TYPE_ID_NNP) {
            if ($scope.nnpMode == $scope.NNP_MODE_DIRECTION) {
                delete data.nnp_country;
                delete data.nnp_region;
                delete data.nnp_city;
                delete data.nnp_operator;
                delete data.nnp_ndc_type;
                delete data.nnp_ndc;
                delete data.nnp_is_default;
                delete data.nnp_use_nnp_ported;
                delete data.nnp_is_exclude_operators;
                delete data.nnp_is_exclude_country;
                delete data.nnp_is_exclude_city;
                delete data.nnp_is_exclude_region;
                delete data.nnp_is_exclude_ndc_type;
                delete data.nnp_is_exclude_ndc;
            } else {
                delete data.nnp_destination;
                delete data.nnp_is_exclude_destination;
            }
        } else {
            data.is_use_ported = false;
        }

        if (!$scope.item.type_id == $scope.TYPE_ID_RN) {
            delete data.rn_country;
            delete data.rn_operator;
            delete data.rn_region;
            delete data.rn_region_fz;
            delete data.rn_is_exclude_country;
            delete data.rn_is_exclude_region;
            delete data.rn_is_exclude_operators;
            delete data.rn_is_exclude_mnc;
            delete data.rn_use_nnp_ported;
        } else if ($scope.item.type_id == $scope.TYPE_ID_RN) {
            data.rn_region_fz = [];
            for (let i of data.rn_region) {
                i = JSON.parse(i);
                data.rn_region_fz.push(i.region_code_fz);
            }
        }

        Prefixlist.save(data).then(function (result) {
            $modalInstance.close();
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
                    $scope.item.nnp_is_exclude_operators = filterData.exclude_operators;
                    $scope.item.nnp_is_default = filterData.is_default;
                    $scope.item.nnp_use_nnp_ported = filterData.use_nnp_ported;
                    $scope.item.nnp_ndc_type = filterData.ndc_type_id;
                    $scope.item.nnp_ndc = filterData.ndc;
                    $scope.item.nnp_country = filterData.country_code;
                    $scope.item.nnp_region = filterData.region_id;
                    $scope.item.nnp_operator = filterData.operator_id;
                    $scope.item.nnp_city = filterData.city_id;
                    $scope.item.nnp_is_exclude_destination = filterData.exclude_destination;
                    $scope.item.nnp_is_exclude_country = filterData.exclude_country;
                    $scope.item.nnp_is_exclude_city = filterData.exclude_city;
                    $scope.item.nnp_is_exclude_region = filterData.exclude_region;
                    $scope.item.nnp_is_exclude_ndc_type = filterData.exclude_ndc_type;
                    $scope.item.nnp_is_exclude_ndc = filterData.exclude_ndc;
                });
            }
        });
    };

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
                    $scope.item.nnp_is_exclude_operators = filterData.exclude_operators;
                    $scope.item.nnp_is_default = filterData.is_default;
                    $scope.item.nnp_use_nnp_ported = filterData.use_nnp_ported;
                    $scope.item.nnp_ndc_type = filterData.ndc_type_id;
                    $scope.item.nnp_ndc = filterData.ndc;
                    $scope.item.nnp_country = filterData.country_code;
                    $scope.item.nnp_region = filterData.region_id;
                    $scope.item.nnp_operator = filterData.operator_id;
                    $scope.item.nnp_city = filterData.city_id;
                    $scope.item.nnp_is_exclude_destination = filterData.exclude_destination;
                    $scope.item.nnp_is_exclude_country = filterData.exclude_country;
                    $scope.item.nnp_is_exclude_city = filterData.exclude_city;
                    $scope.item.nnp_is_exclude_region = filterData.exclude_region;
                    $scope.item.nnp_is_exclude_ndc_type = filterData.exclude_ndc_type;
                    $scope.item.nnp_is_exclude_ndc = filterData.exclude_ndc;
                });
            }
        });
    };

    $scope.setNnpFields = function (data) {
        if ($scope.item.type_id == $scope.TYPE_ID_NNP) {
            try {
                var filterData = $.parseJSON($scope.item.nnp_filter_json);

                $scope.item.nnp_destination = filterData.nnp_destination_id;
                $scope.item.nnp_is_exclude_operators = filterData.exclude_operators;
                $scope.item.nnp_is_exclude_destination = filterData.exclude_destination;
                $scope.item.nnp_is_exclude_country = filterData.exclude_country;
                $scope.item.nnp_is_exclude_city = filterData.exclude_city;
                $scope.item.nnp_is_exclude_region = filterData.exclude_region;
                $scope.item.nnp_is_exclude_ndc_type = filterData.exclude_ndc_type;
                $scope.item.nnp_is_exclude_ndc = filterData.exclude_ndc_type;
                $scope.item.nnp_ndc_type = filterData.ndc_type_id;
                $scope.item.nnp_is_default = filterData.is_default;
                $scope.item.nnp_use_nnp_ported = filterData.use_nnp_ported;

                if (filterData.country_code) {
                    $scope.nnpMode = $scope.NNP_MODE_FILTER;
                }

                $scope.item.nnp_country = filterData.country_code;

                if ($scope.item.nnp_country) {
                    var regionList = Nnp.regionList({country_code: $scope.item.nnp_country}).then(function (data) {
                        regionLoadComplete = true;
                        $scope.regionList = data;
                        $scope.item.nnp_region = filterData.region_id;

                        if ($scope.item.nnp_region) {
                            $scope.item.nnp_city = filterData.city_id;
                            Nnp.cityList({
                                country_code: $scope.item.nnp_country,
                                region: $scope.item.nnp_region
                            }).then(function (data) {
                                $scope.cityList = data;
                                $scope.cities = data;
                            });
                        }
                    });

                    var operatorList = Nnp.operatorList({country_code: $scope.item.nnp_country}).then(function (data) {
                        $scope.operatorList = data;
                        $scope.item.nnp_operator = filterData.operator_id;
                    });

                    var ndcList = Nnp.ndcList({country_code: $scope.item.nnp_country}).then(function (data) {
                        $scope.ndcList = data;
                        $scope.item.nnp_ndc = filterData.ndc;
                    });

                    $q.all([regionList, operatorList, ndcList]).then(function () {
                        $scope.saveEnabled = true;
                    });
                } else {
                    $scope.saveEnabled = true;
                }
            } catch (error) {
                $scope.nnpDataParseError = true;
            }

        }
    };

    $scope.setVoipRegistryFields = function (data) {
        if ($scope.item.type_id == $scope.TYPE_ID_VOIP_REGISTRY) {
            try {
                var filterData = $.parseJSON($scope.item.nnp_filter_json);

                $scope.item.registry_country = filterData.country_code;
                $scope.item.registry_ndc_type = filterData.ndc_type_id;
                $scope.item.registry_source = filterData.source;

                if ($scope.item.registry_country) {
                    Nnp.cityList({country_code: $scope.item.registry_country}).then(function (data) {
                        $scope.cityList = data;
                        $scope.cities = data;
                        $scope.item.registry_city = filterData.city_id;
                        $scope.saveEnabled = true;
                    });
                } else {
                    $scope.saveEnabled = true;
                }
            } catch (error) {
                $scope.nnpDataParseError = true;
            }
        }
    };

    $scope.setVoipNumberFields = function (data) {
        if ($scope.item.type_id == $scope.TYPE_ID_VOIP_NUMBER) {
            try {
                var filterData = $.parseJSON($scope.item.nnp_filter_json);

                $scope.item.number_country = filterData.country_code;
                $scope.item.number_ndc_type = filterData.ndc_type_id;
                $scope.item.number_source = filterData.source;
                $scope.item.number_status = filterData.status;

                $scope.item.number_exclude_country = filterData.exclude_country_code;
                $scope.item.number_exclude_region = filterData.exclude_region_id;
                $scope.item.number_exclude_city = filterData.exclude_city_id;
                $scope.item.number_exclude_ndc_type = filterData.exclude_ndc_type_id;
                $scope.item.number_exclude_source = filterData.exclude_source;
                $scope.item.number_exclude_status = filterData.exclude_status;
                $scope.item.number_exclude_statuses = filterData.exclude_statuses ? filterData.exclude_statuses : null;

                if ($scope.item.number_country) {
                    Nnp.regionList({country_code: $scope.item.number_country}).then(function (data) {
                        regionLoadComplete = true;
                        $scope.regionList = data;
                        $scope.item.number_region = filterData.region_id;

                        if ($scope.item.number_region) {
                            $scope.item.number_city = filterData.city_id;
                            Nnp.cityList({
                                country_code: $scope.item.number_country,
                                region: $scope.item.number_region
                            }).then(function (data) {
                                $scope.cityList = data;
                                $scope.cities = data;
                                $scope.saveEnabled = true;
                            });
                        } else {
                            $scope.saveEnabled = true;
                        }
                    });
                } else {
                    $scope.saveEnabled = true;
                }
            } catch (error) {
                $scope.nnpDataParseError = true;
            }
        }
    };

    $scope.setVoipNumberFieldsV2 = function (data) {
        if ($scope.item.type_id == $scope.TYPE_ID_VOIP_NUMBER) {
            try {
                var filterData = $.parseJSON($scope.item.nnp_filter_json);

                $scope.item.number_countries = filterData.country_codes ? filterData.country_codes : null;
                $scope.item.number_ndc_types = filterData.ndc_type_ids ? filterData.ndc_type_ids : null;
                $scope.item.number_sources = filterData.sources ? filterData.sources : null ;
                $scope.item.number_statuses = filterData.statuses ? filterData.statuses : null;

                $scope.item.number_exclude_countries = filterData.exclude_country_codes ? filterData.exclude_country_codes : null ;
                $scope.item.number_exclude_regions = filterData.exclude_region_ids ? filterData.exclude_region_ids : null;
                $scope.item.number_exclude_cities = filterData.exclude_city_ids ? filterData.exclude_city_ids : null;
                $scope.item.number_exclude_ndc_types = filterData.exclude_ndc_type_ids ? filterData.exclude_ndc_type_ids : null;
                $scope.item.number_exclude_sources = filterData.exclude_sources ? filterData.exclude_sources : null;
                $scope.item.number_exclude_statuses = filterData.exclude_statuses ? filterData.exclude_statuses : null;

                if ($scope.item.number_countries) {
                    Nnp.regionList({country_code: $scope.item.number_countries}).then(function (data) {
                        regionLoadCompleteV2 = true;
                        $scope.regionListV2 = data;
                        $scope.item.number_regions = filterData.region_ids ? filterData.region_ids : null ;

                        if ($scope.item.number_regions) {
                            $scope.item.number_cities = filterData.city_ids;
                            Nnp.cityList({
                                country_code: $scope.item.number_countries,
                                region: $scope.item.number_regions
                            }).then(function (data) {
                                $scope.cityListV2 = data;
                                $scope.cities = data;
                                $scope.saveEnabled = true;
                            });
                        } else {
                            $scope.saveEnabled = true;
                        }
                    });
                } else {
                    $scope.saveEnabled = true;
                }
            } catch (error) {
                $scope.nnpDataParseError = true;
            }
        }
    };

    $scope.setGtFields = function (data) {
        if ($scope.item.type_id == $scope.TYPE_ID_GT) {
            try {
                var filterData = $.parseJSON($scope.item.nnp_filter_json);

                $scope.item.gt_country = filterData.country_code;
                $scope.item.gt_is_exclude_country = filterData.exclude_country;
                $scope.item.gt_is_exclude_region = filterData.exclude_region;
                $scope.item.gt_is_exclude_operators = filterData.exclude_operators;

                if ($scope.item.gt_country) {
                    var regionList = Nnp.regionList({country_code: $scope.item.gt_country}).then(function (data) {
                        gtRegionLoadComplete = true;
                        $scope.gtRegionList = data;
                        $scope.item.gt_region = filterData.region_id;
                    });

                    var operatorList = Nnp.operatorList({country_code: $scope.item.gt_country}).then(function (data) {
                        $scope.gtOperatorList = data;
                        $scope.item.gt_operator = filterData.operator_id;
                    });

                    $q.all([regionList, operatorList]).then(function () {
                        $scope.saveEnabled = true;
                    });
                } else {
                    $scope.saveEnabled = true;
                }
            } catch (error) {
                $scope.nnpDataParseError = true;
            }
        }
    };

    $scope.setRnFields = function (data) {
        if ($scope.item.type_id == $scope.TYPE_ID_RN) {
            try {
                var filterData = $.parseJSON($scope.item.nnp_filter_json);
                $scope.item.rn_country = filterData.country_code;
                $scope.item.rn_is_exclude_country = filterData.exclude_country;
                $scope.item.rn_is_exclude_region = filterData.exclude_region_code_fz;
                $scope.item.rn_is_exclude_operators = filterData.exclude_operators;
                $scope.item.rn_is_exclude_mnc = filterData.exclude_mnc;
                $scope.item.rn_use_nnp_ported = filterData.use_nnp_ported;

                $scope.item.rn_route_mnc = filterData.mnc;
                if ($scope.item.rn_country) {
                    var regionList = Nnp.regionList({country_code: $scope.item.rn_country}).then(function (data) {
                        rnRegionLoadComplete = true;
                        $scope.rnRegionList = data;
                        $scope.item.rn_region = filterData.region_code;
                        $scope.item.rn_region_fz = filterData.region_code_fz;
                    });

                    var operatorList = Nnp.operatorList({country_code: $scope.item.rn_country}).then(function (data) {
                        $scope.rnOperatorList = data;
                        $scope.item.rn_operator = filterData.operator_id;
                    });

                    $q.all([regionList, operatorList]).then(function () {
                        $scope.saveEnabled = true;
                    });
                } else  {
                    $scope.saveEnabled = true;
                }
            } catch (error) {
                $scope.nnpDataParseError = true;
            }
        }
    };

    $scope.back = function () {
        $modalInstance.close();
    };

    $scope.clickNumberItem = function (item) {
        if (window.getSelection().type == 'Range') return;

        Redirect.numberEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.clickTrunkItem = function (item) {
        if (window.getSelection().type == 'Range') return;

        Redirect.trunkEdit(item.id).then(function () {
            $scope.init();
        });
    };
};
