var PricelistShortViewCtrl = function ($scope, Redirect, List, Pricelist, PricelistLocation, PricelistFilterA, PricelistFilterB, PricelistPrefixPrice, params, $modalInstance, $window) {

    $scope.locationIds = List.location();

    $scope.limit = 10;
    $scope.prefixes = [];

    $scope.drawTable = function (data) {
        $scope.list = [];

        $scope.item.id = data.id;

        $scope.list.push({
            id: data.id,
            is_pricelist: true,
            name: data.description ? data.description : (data.name + ', валюта ' + data.currency_id),
            date_created: data.date_created,
            date_start: data.date_start,
            is_active: data.is_active,
            parent_id: data.parent_id,
            orig: data.orig
        });

        $scope.pricelistIsActive = data.is_active;
        $scope.pricelistDateStart = data.date_start;

        for (var locationKey in data.location) {
            var item = data.location[locationKey];

            var isBasic = (item.id == $scope.item.basic_pricelist_location_id);

            var locationText = item.description ? item.description : ((isBasic ? 'Базовое местоположение: ' : 'Местоположение: ') +
                $scope.locationIds.find(function(element) {return element.id == item.location_id;}).name +
                ((item.mcc_string == '' || item.mcc_string == null) ? '' : ', MCC: ' + item.mcc_string) +
                ((item.mnc_string == '' || item.mnc_string == null) ? '' : ', MNC: ' + item.mnc_string) +
                ((item.delta_price == '' || item.delta_price == null) ? '' : ', Наценка: ' + item.delta_price));

            $scope.list.push({
                is_location: true,
                id: item.id,
                location_text: locationText,
                has_children: data.location[locationKey].filterA.length > 0,
                is_basic: isBasic
            });

            for (var filterAKey in data.location[locationKey].filterA) {
                var item = data.location[locationKey].filterA[filterAKey];
                var filterAName = $scope.formFilterText(item);
                var filterAId = item.id;

                var hasFilterAHeader = false;
                var totalPrefixCount = 0;

                for (var filterBKey in data.location[locationKey].filterA[filterAKey].filterB) {
                    var item = data.location[locationKey].filterA[filterAKey].filterB[filterBKey];
                    totalPrefixCount += item.prefixPriceNoLimit.length;
                }

                for (var filterBKey in data.location[locationKey].filterA[filterAKey].filterB) {
                    var item = data.location[locationKey].filterA[filterAKey].filterB[filterBKey];
                    var filterBName = $scope.formFilterText(item);
                    var filterBId = item.id;

                    var prefixCount = item.prefixPriceNoLimit.length;
                    var interconnectPrice = isNaN(parseFloat(item.interconnect_price)) ? 0 : parseFloat(item.interconnect_price);

                    var hasFilterBHeader = false;

                    for (var prefixPriceKey in data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPriceNoLimit) {
                        var item = data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPriceNoLimit[prefixPriceKey];

                        if (!hasFilterBHeader) {
                            $scope.list.push({
                                is_filter_b_header: true,
                                is_filter_a_header: !hasFilterAHeader,
                                filter_a_name: filterAName,
                                filter_b_name: filterBName,
                                filter_a_id: filterAId,
                                filter_b_id: filterBId,
                                is_prefix_price: true,
                                prefix_price_id: item.id,
                                b_number_price: (parseFloat(item.b_number_price) * 1000000 + interconnectPrice * 1000000) / 1000000,
                                old_b_number_price: (parseFloat(item.old_b_number_price) * 1000000 + interconnectPrice * 1000000) / 1000000,
                                prefix_b: item.prefix_b,
                                prefix_count: prefixCount,
                                total_prefix_count: totalPrefixCount,
                                interconnect_price: parseFloat(interconnectPrice)
                            });

                            hasFilterBHeader = true;
                        } else {
                            $scope.list.push({
                                is_filter_b_header: false,
                                is_filter_a_header: !hasFilterAHeader,
                                is_prefix_price: true,
                                prefix_price_id: item.id,
                                b_number_price: (parseFloat(item.b_number_price) * 1000000 + interconnectPrice * 1000000) / 1000000,
                                old_b_number_price: (parseFloat(item.old_b_number_price) * 1000000 + interconnectPrice * 1000000) / 1000000,
                                prefix_b: item.prefix_b
                            });
                        }

                        hasFilterAHeader = true;
                    }
                }
            }
        }
    };

    $scope.formFilterText = function (item) {
        if (item.description) {
            return item.description;
        }

        var filterText;

        if (item.nnp_country == '{}' && item.f_inv_nnp_country ||
            item.nnp_city == '{}' && item.f_inv_nnp_city ||
            item.nnp_destination == '{}' && item.f_inv_nnp_destination ||
            item.nnp_region == '{}' && item.f_inv_nnp_region ||
            item.nnp_ndc_type == '{}' && item.f_inv_nnp_ndc_type ||
            item.nnp_operator == '{}' && item.f_inv_nnp_operator) {
            filterText = 'Запрещено все!'
        } else {
            filterText = ((item.nnp_country_name == null) ? '' : (item.f_inv_nnp_country ? ('Кроме: ' + item.nnp_country_name) : item.nnp_country_name)) +
                ((item.nnp_ndc_type_name == null) ? '' : (item.f_inv_nnp_ndc_type ? (' Кроме: ' + item.nnp_ndc_type_name) : (' ' + item.nnp_ndc_type_name))) +
                ((item.nnp_operator_name == null) ? '' : (item.f_inv_nnp_operator ? (' Кроме: ' + item.nnp_operator_name) : (' ' + item.nnp_operator_name))) +
                ((item.nnp_region_name == null) ? '' : (item.f_inv_nnp_region ? (' Кроме: ' + item.nnp_region_name) : (' ' + item.nnp_region_name))) +
                ((item.nnp_city_name == null) ? '' : (item.f_inv_nnp_city ? (' Кроме: ' + item.nnp_city_name) : (' ' + item.nnp_city_name)));

            if (!filterText) {
                filterText = item.f_inv_nnp_destination ? ('Кроме: ' + item.nnp_destination_name) : item.nnp_destination_name;
            }

            if (!filterText) {
                filterText = '--';
            }
        }

        return filterText;
    };

    $scope.initData = function (id) {
        Pricelist.getWithDependentsNoLimit({id: id}).then(function (data) {
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
        $modalInstance.dismiss();
    };

    $scope.viewPricelist = function (id) {
        Redirect.pricelistView(id).then(function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.editLocation = function (id) {
        Redirect.pricelistLocationEdit(id, $scope.pricelistIsActive).then(function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.editFilterA = function (id) {
        Redirect.pricelistFilterAEdit(id, $scope.pricelistIsActive).then(function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.editFilterB = function (id) {
        Redirect.pricelistFilterBEdit(id, $scope.pricelistIsActive).then(function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.editPrefixPrice = function (id) {
        Redirect.pricelistPrefixPriceEdit(id, $scope.pricelistIsActive).then(function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.displayEmptyAlert = function () {
        alert('Это пустая строка.');
    };
};