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
            name: data.name,
            currency_id: data.currency_id
        });

        for (var locationKey in data.location) {
            var item = data.location[locationKey];

            $scope.list.push({
                is_location: true,
                id: item.id,
                location_id: $scope.locationIds.find(function(element) {return element.id == item.location_id;}).name,
                mcc: item.mcc_string,
                mnc: item.mnc_string,
                has_children: data.location[locationKey].filterA.length > 0
            });

            for (var filterAKey in data.location[locationKey].filterA) {
                var item = data.location[locationKey].filterA[filterAKey];

                var filterAName = ((item.nnp_country_name == null) ? '' : item.nnp_country_name) +
                    ((item.nnp_ndc_type_name == null) ? '' : (' ' + item.nnp_ndc_type_name)) +
                    ((item.nnp_operator_name == null) ? '' : (' ' + item.nnp_operator_name)) +
                    ((item.nnp_region_name == null) ? '' : (' ' + item.nnp_region_name)) +
                    ((item.nnp_city_name == null) ? '' : (' ' + item.nnp_city_name));

                if (!filterAName) {
                    filterAName = item.nnp_destination_name;
                }

                if (!filterAName) {
                    filterAName = '--';
                }

                var hasFilterAHeader = false;
                var totalPrefixCount = 0;

                for (var filterBKey in data.location[locationKey].filterA[filterAKey].filterB) {
                    var item = data.location[locationKey].filterA[filterAKey].filterB[filterBKey];
                    totalPrefixCount += item.prefixPriceNoLimit.length;
                }

                for (var filterBKey in data.location[locationKey].filterA[filterAKey].filterB) {
                    var item = data.location[locationKey].filterA[filterAKey].filterB[filterBKey];

                    var filterBName = ((item.nnp_country_name == null) ? '' : item.nnp_country_name) +
                        ((item.nnp_ndc_type_name == null) ? '' : (' ' + item.nnp_ndc_type_name)) +
                        ((item.nnp_operator_name == null) ? '' : (' ' + item.nnp_operator_name)) +
                        ((item.nnp_region_name == null) ? '' : (' ' + item.nnp_region_name)) +
                        ((item.nnp_city_name == null) ? '' : (' ' + item.nnp_city_name));

                    if (!filterBName) {
                        filterBName = item.nnp_destination_name;
                    }

                    if (!filterBName) {
                        filterBName = '--';
                    }

                    var prefixCount = item.prefixPriceNoLimit.length;

                    var hasFilterBHeader = false;

                    for (var prefixPriceKey in data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPriceNoLimit) {
                        var item = data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPriceNoLimit[prefixPriceKey];

                        if (!hasFilterBHeader) {
                            $scope.list.push({
                                is_filter_b_header: true,
                                is_filter_a_header: !hasFilterAHeader,
                                filter_a_name: filterAName,
                                filter_b_name: filterBName,
                                is_prefix_price: true,
                                prefix_price_id: item.id,
                                b_number_price: item.b_number_price,
                                prefix_b: item.prefix_b,
                                prefix_count: prefixCount,
                                total_prefix_count: totalPrefixCount
                            });

                            hasFilterBHeader = true;
                        } else {
                            $scope.list.push({
                                is_filter_b_header: false,
                                is_filter_a_header: !hasFilterAHeader,
                                is_prefix_price: true,
                                prefix_price_id: item.id,
                                b_number_price: item.b_number_price,
                                prefix_b: item.prefix_b
                            });
                        }

                        hasFilterAHeader = true;
                    }
                }
            }
        }
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
            $scope.initData(id);
        });
    };

    $scope.editPrefixPrice = function (id) {
        Redirect.pricelistPrefixPriceEdit(id).then(function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.displayEmptyAlert = function () {
        alert('Это пустая строка.');
    };
};