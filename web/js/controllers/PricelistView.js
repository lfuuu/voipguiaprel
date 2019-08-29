var PricelistViewCtrl = function ($scope, Redirect, List, Pricelist, PricelistLocation, PricelistFilterA, PricelistFilterB, PricelistPrefixPrice, params, $modalInstance, $window) {

    $scope.locationIds = List.location();

    $scope.limit = 10;
    $scope.prefixes = [];

    $scope.drawTable = function (data) {
        $scope.list = [];

        $scope.list.push({
            is_pricelist_header: true
        });

        $scope.item.id = data.id;

        $scope.list.push({
            is_pricelist_header_columns: true
        });

        var minimumMarginTypeName = 'Нет';

        if (data.minimum_margin_type == 1) {
            minimumMarginTypeName = 'Деньги';
        } else if (data.minimum_margin_type == 2) {
          minimumMarginTypeName = 'Процент';
        }

        $scope.list.push({
            id: data.id,
            is_pricelist: true,
            name: data.name,
            currency_id: data.currency_id,
            date_start: data.date_start,
            date_end: data.date_end,
            orig: data.orig ? 'Оригинация' : 'Терминация',
            is_global: data.is_global ? 'Да' : 'Нет',
            is_active: data.is_active ? 'Да' : 'Нет',
            version: data.pricelist_version,
            minimum_margin: data.minimum_margin,
            minimum_margin_type: minimumMarginTypeName
        });

        $scope.pricelistIsActive = data.is_active;
        $scope.pricelistDateStart = data.date_start;
        $scope.pricelistDefaultTarificationFreeSeconds = data.default_tarification_free_seconds;
        $scope.pricelistDefaultTarificationIntervalSeconds = data.default_tarification_interval_seconds;
        $scope.pricelistDefaultTarificationMinPaidSeconds = data.default_tarification_min_paid_seconds;
        $scope.pricelistServiceTypeId = data.service_type_id;
        $scope.pricelistDefaultTarificationType = data.default_tarification_type;

        var locationHeaderSet = false;

        for (var locationKey in data.location) {
            var item = data.location[locationKey];

            if (!locationHeaderSet) {
                $scope.list.push({
                    is_location_header: true
                });

                $scope.list.push({
                    is_location_header_columns: true
                });

                locationHeaderSet = true;
            }

            var locationId = item.id;

            $scope.list.push({
                is_location: true,
                id: item.id,
                parent_id: $scope.item.id,
                location_id: $scope.locationIds.find(function(element) {return element.id == item.location_id;}).name,
                mcc: item.mcc_string,
                mnc: item.mnc_string,
                delta_price: item.delta_price,
                rounding_threshold: item.rounding_threshold,
                has_children: data.location[locationKey].filterA.length > 0,
                pricelist_service_type_id: $scope.pricelistServiceTypeId
            });

            var filterAHeaderSet = false;

            for (var filterAKey in data.location[locationKey].filterA) {
                var item = data.location[locationKey].filterA[filterAKey];

                if (!filterAHeaderSet) {
                    $scope.list.push({
                        is_filter_a_header: true
                    });

                    $scope.list.push({
                        is_filter_a_header_columns: true
                    });

                    filterAHeaderSet = true;
                }

                locationHeaderSet = false;

                var filterAId = item.id;

                $scope.list.push({
                    is_filter_a: true,
                    id: item.id,
                    parent_id: locationId,
                    mode_selected: item.mode_selected ? 'Выбранные' : 'Кроме выбранных',
                    nnp_destination: (item.nnp_destination == '{}' && item.f_inv_nnp_destination) ? 'Запрещено все!' : (item.f_inv_nnp_destination ? ('Кроме: ' + item.nnp_destination_name) : item.nnp_destination_name),
                    nnp_country: (item.nnp_country == '{}' && item.f_inv_nnp_country) ? 'Запрещено все!' : (item.f_inv_nnp_country ? ('Кроме: ' + item.nnp_country_name) : item.nnp_country_name),
                    nnp_operator: (item.nnp_operator == '{}' && item.f_inv_nnp_operator) ? 'Запрещено все!' : (item.f_inv_nnp_operator ? ('Кроме: ' + item.nnp_operator_name) : item.nnp_operator_name),
                    nnp_region: (item.nnp_region == '{}' && item.f_inv_nnp_region) ? 'Запрещено все!' : (item.f_inv_nnp_region ? ('Кроме: ' + item.nnp_region_name) : item.nnp_region_name),
                    nnp_city: (item.nnp_city == '{}' && item.f_inv_nnp_city) ? 'Запрещено все!' : (item.f_inv_nnp_city ? ('Кроме: ' + item.nnp_city_name) : item.nnp_city_name),
                    nnp_ndc_type: (item.nnp_ndc_type == '{}' && item.f_inv_nnp_ndc_type) ? 'Запрещено все!' : (item.f_inv_nnp_ndc_type ? ('Кроме: ' + item.nnp_ndc_type_name) : item.nnp_ndc_type_name),
                    time_start: item.time_start,
                    time_end: item.time_end,
                    has_children: data.location[locationKey].filterA[filterAKey].filterB.length > 0
                });

                var filterBHeaderSet = false;

                for (var filterBKey in data.location[locationKey].filterA[filterAKey].filterB) {
                    var item = data.location[locationKey].filterA[filterAKey].filterB[filterBKey];

                    if (!filterBHeaderSet) {
                        $scope.list.push({
                            is_filter_b_header: true
                        });

                        $scope.list.push({
                            is_filter_b_header_columns: true
                        });

                        filterBHeaderSet = true;
                    }

                    filterAHeaderSet = false;

                    var filterBItem = {
                        is_filter_b: true,
                        id: item.id,
                        parent_id: filterAId,
                        mode_selected: item.mode_selected ? 'Выбранные' : 'Кроме выбранных',
                        nnp_destination: (item.nnp_destination == '{}' && item.f_inv_nnp_destination) ? 'Запрещено все!' : (item.f_inv_nnp_destination ? ('Кроме: ' + item.nnp_destination_name) : item.nnp_destination_name),
                        nnp_country: (item.nnp_country == '{}' && item.f_inv_nnp_country) ? 'Запрещено все!' : (item.f_inv_nnp_country ? ('Кроме: ' + item.nnp_country_name) : item.nnp_country_name),
                        nnp_operator: (item.nnp_operator == '{}' && item.f_inv_nnp_operator) ? 'Запрещено все!' : (item.f_inv_nnp_operator ? ('Кроме: ' + item.nnp_operator_name) : item.nnp_operator_name),
                        nnp_region: (item.nnp_region == '{}' && item.f_inv_nnp_region) ? 'Запрещено все!' : (item.f_inv_nnp_region ? ('Кроме: ' + item.nnp_region_name) : item.nnp_region_name),
                        nnp_city: (item.nnp_city == '{}' && item.f_inv_nnp_city) ? 'Запрещено все!' : (item.f_inv_nnp_city ? ('Кроме: ' + item.nnp_city_name) : item.nnp_city_name),
                        nnp_ndc_type: (item.nnp_ndc_type == '{}' && item.f_inv_nnp_ndc_type) ? 'Запрещено все!' : (item.f_inv_nnp_ndc_type ? ('Кроме: ' + item.nnp_ndc_type_name) : item.nnp_ndc_type_name),
                        interconnect_price: item.interconnect_price,
                        ported_num_price: item.ported_num_price,
                        tarification_free_seconds: item.tarification_free_seconds,
                        tarification_interval_seconds: item.tarification_interval_seconds,
                        tarification_min_paid_seconds: item.tarification_min_paid_seconds,
                        tarification_type: item.tarification_type,
                        time_start: item.time_start,
                        time_end: item.time_end,
                        use_for_minimum: item.use_for_minimum ? 'Да' : 'Нет',
                        has_children: data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPrice.length > 0
                    };

                    $scope.list.push(filterBItem);

                    var prefixPriceHeaderSet = false;

                    for (var prefixPriceKey in data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPrice) {
                        if (!prefixPriceHeaderSet) {
                            $scope.list.push({
                                is_prefix_price_header: true
                            });

                            $scope.list.push({
                                is_prefix_price_header_columns: true
                            });

                            prefixPriceHeaderSet = true;
                        }

                        filterBHeaderSet = false;

                        var item = data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPrice[prefixPriceKey];

                        $scope.list.push({
                            is_prefix_price: true,
                            filter_b_id: filterBItem.id,
                            prefix_price_id: item.id,
                            b_number_price: item.b_number_price,
                            change_flag: item.change_flag,
                            prefix_b: item.prefix_b,
                            date_from: item.date_from,
                            date_to: item.date_to,
                            has_buttons: true
                        });

                    }

                    if (typeof data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPriceCount[0] !== 'undefined' &&
                        data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPriceCount[0].total_count > $scope.limit) {
                        $scope.list.push({
                            is_prefix_price_footer: true,
                            totalCount: data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPriceCount[0].total_count,
                            currentPage: 1,
                            offset: 0,
                            filter_b_id: filterBItem.id
                        });
                    }
                }
            }
        }
    };

    $scope.initData = function (id) {
        Pricelist.getWithDependents({id: id}).then(function (data) {
            $scope.item = data;
            $scope.drawTable(data);
        });
    };

    $scope.getPrefixPriceList = function (filter_b_id, page) {
        PricelistPrefixPrice.read({pricelist_filter_b_id: filter_b_id, page_number: page}).then(function (data) {
            $scope.prefixes[filter_b_id] = data;

            var index = '';

            for (var i in $scope.list) {
                if ($scope.list[i].is_prefix_price == true && $scope.list[i].filter_b_id == filter_b_id) {
                    if (index == '') {
                        index = i;
                        break;
                    }
                }
            }

            $scope.list.splice(index, $scope.limit);

            for (var i = $scope.limit - 1; i >= 0; i--) {
                var item = $scope.prefixes[filter_b_id][i];

                if (typeof item !== 'undefined') {
                    $scope.list.splice(index, 0, {
                        is_prefix_price: true,
                        filter_b_id: filter_b_id,
                        prefix_price_id: item.id,
                        b_number_price: item.b_number_price,
                        change_flag: item.change_flag,
                        prefix_b: item.prefix_b,
                        date_from: item.date_from,
                        date_to: item.date_to,
                        has_buttons: true
                    });
                } else {
                    $scope.list.splice(index, 0, {
                        is_prefix_price: true,
                        filter_b_id: filter_b_id,
                        prefix_price_id: '',
                        b_number_price: '',
                        change_flag: '',
                        prefix_b: '',
                        date_from: '',
                        date_to: '',
                        has_buttons: false
                    });
                }
            }
        });
    };

    $scope.setPagingData = function (page, filterBId) {
        $scope.getPrefixPriceList(filterBId, page);
    };

    if (params.id) {
        $scope.initData(params.id);
    } else {
        $scope.item = {};
    }

    $scope.back = function () {
        $modalInstance.close();
    };

    $scope.addLocation = function (pricelistId) {
        Redirect.pricelistLocationCreate(pricelistId).then(function () {
            $scope.initData($scope.item.id);
        }, function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.addFilterA = function (locationId) {
        Redirect.pricelistFilterACreate(locationId).then(function () {
            $scope.initData($scope.item.id);
        }, function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.addFilterB = function (filterAId) {
        Redirect.pricelistFilterBCreate(
            filterAId,
            $scope.pricelistIsActive,
            $scope.pricelistDefaultTarificationFreeSeconds,
            $scope.pricelistDefaultTarificationIntervalSeconds,
            $scope.pricelistDefaultTarificationMinPaidSeconds,
            $scope.pricelistDefaultTarificationType
        ).then(function () {
            $scope.initData($scope.item.id);
        }, function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.addPrefixPrice = function (filterBId) {
        Redirect.pricelistPrefixPriceCreate(filterBId, $scope.pricelistDateStart, $scope.pricelistIsActive, $scope.item.id).then(function () {
            $scope.initData($scope.item.id);
        }, function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.editPricelist = function (id) {
        Redirect.pricelistEdit(id).then(function () {
            $scope.initData($scope.item.id);
        }, function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.editLocation = function (id) {
        Redirect.pricelistLocationEdit(id, $scope.pricelistIsActive, $scope.pricelistServiceTypeId).then(function () {
            $scope.initData($scope.item.id);
        }, function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.editFilterA = function (id) {
        Redirect.pricelistFilterAEdit(id).then(function () {
            $scope.initData($scope.item.id);
        }, function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.editFilterB = function (id) {
        Redirect.pricelistFilterBEdit(id).then(function () {
            $scope.initData($scope.item.id);
        }, function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.editPrefixPrice = function (id) {
        Redirect.pricelistPrefixPriceEdit(id, $scope.pricelistIsActive, $scope.item.id).then(function () {
            $scope.initData($scope.item.id);
        }, function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.deleteLocation = function (id) {
        if (!$window.confirm('Удалить местоположение прайслиста?')) return;

        PricelistLocation.delete(id).then(function(response) {
            $scope.initData($scope.item.id);
        });
    };

    $scope.deleteFilterA = function (id) {
        if (!$window.confirm('Удалить фильтр A?')) return;

        PricelistFilterA.delete(id).then(function(response) {
            $scope.initData($scope.item.id);
        });
    };

    $scope.deleteFilterB = function (id) {
        if (!$window.confirm('Удалить фильтр B?')) return;

        PricelistFilterB.delete(id).then(function(response) {
            $scope.initData($scope.item.id);
        });
    };

    $scope.deletePrefixPrice = function (id) {
        if (!$window.confirm('Удалить прайс на префикс?')) return;

        PricelistPrefixPrice.delete(id).then(function(response) {
            $scope.initData($scope.item.id);
        });
    };

    $scope.printToExcel = function () {
        window.open('/pricelist/excel?id=' + $scope.item.id + '&server_id=1&factor=' + factor,'_blank');
    };

    $scope.displayEmptyAlert = function () {
        alert('Это пустая строка.');
    };
};