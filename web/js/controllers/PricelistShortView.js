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
            name: data.description ? (data.description + ', валюта ' + data.currency_id) : ('Валюта ' + data.currency_id),
            date_created: data.date_created,
            date_start: data.date_start,
            is_active: data.is_active,
            parent_id: data.parent_id,
            orig: data.orig
        });

        $scope.pricelistIsActive = data.is_active;
        $scope.pricelistDateStart = data.date_start;
        $scope.pricelistServiceTypeId = data.service_type_id;
        $scope.pricelistName = data.name;

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

                var totalSimplifiedPrefixList = {};

                for (var filterBCountKey in data.location[locationKey].filterA[filterAKey].filterB) {
                    for (var prefixPriceCountKey in data.location[locationKey].filterA[filterAKey].filterB[filterBCountKey].prefixPriceNoLimit) {
                        var prefixItem = data.location[locationKey].filterA[filterAKey].filterB[filterBCountKey].prefixPriceNoLimit[prefixPriceCountKey];

                        if (totalSimplifiedPrefixList[prefixItem.pricelist_filter_b_id + '_' + prefixItem.prefix_b]) {
                            //do_nothing
                        } else {
                            totalSimplifiedPrefixList[prefixItem.pricelist_filter_b_id + '_' + prefixItem.prefix_b] = true;

                            totalPrefixCount++;
                        }
                    }
                }

                for (var filterBKey in data.location[locationKey].filterA[filterAKey].filterB) {
                    var item = data.location[locationKey].filterA[filterAKey].filterB[filterBKey];
                    var filterBName = $scope.formFilterText(item);
                    var filterBId = item.id;

                    var interconnectPrice = isNaN(parseFloat(item.interconnect_price)) ? 0 : parseFloat(item.interconnect_price);

                    var hasFilterBHeader = false;

                    var simplifiedPrefixList = {};
                    var prefixCount = 0;

                    for (var prefixPriceKey in data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPriceNoLimit) {
                        var prefixItem = data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPriceNoLimit[prefixPriceKey];
                        var bNumberPrice = ((parseFloat(prefixItem.b_number_price) * 1000000 + interconnectPrice * 1000000) / 1000000).toFixed(4);

                        if (simplifiedPrefixList[prefixItem.prefix_b]) {
                            var previousItem = simplifiedPrefixList[prefixItem.prefix_b][(simplifiedPrefixList[prefixItem.prefix_b].length - 1)];
                            var previousPrice = parseFloat(previousItem.b_number_price);

                            simplifiedPrefixList[prefixItem.prefix_b].push({
                                prefix_price_id: prefixItem.id,
                                b_number_price: bNumberPrice,
                                date_from: prefixItem.date_from,
                                price_change: previousPrice > bNumberPrice ? 'decrease' : 'increase'
                            });
                        } else {
                            simplifiedPrefixList[prefixItem.prefix_b] = [{
                                prefix_price_id: prefixItem.id,
                                b_number_price: bNumberPrice,
                                date_from: prefixItem.date_from,
                                price_change: 'none'
                            }];

                            prefixCount++;
                        }
                    }

                    for (var prefixB in simplifiedPrefixList) {
                        var item = simplifiedPrefixList[prefixB];

                        if (!hasFilterBHeader) {
                            $scope.list.push({
                                is_filter_b_header: true,
                                is_filter_a_header: !hasFilterAHeader,
                                filter_a_name: filterAName,
                                filter_b_name: filterBName,
                                filter_a_id: filterAId,
                                filter_b_id: filterBId,
                                is_prefix_price: true,
                                prefix_b: prefixB == 'null' ? '' : prefixB,
                                prefix_count: prefixCount,
                                total_prefix_count: totalPrefixCount,
                                interconnect_price: parseFloat(interconnectPrice),
                                prefixes: item
                            });

                            hasFilterBHeader = true;
                        } else {
                            $scope.list.push({
                                is_filter_b_header: false,
                                is_filter_a_header: !hasFilterAHeader,
                                is_prefix_price: true,
                                prefix_b: prefixB == 'null' ? '' : prefixB,
                                prefixes: item
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
        }, function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.editLocation = function (id) {
        Redirect.pricelistLocationEdit(id, $scope.pricelistIsActive).then(function () {
            $scope.initData($scope.item.id);
        }, function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.editFilterA = function (id) {
        Redirect.pricelistFilterAEdit(id, $scope.pricelistIsActive).then(function () {
            $scope.initData($scope.item.id);
        }, function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.editFilterB = function (id) {
        Redirect.pricelistFilterBEdit(id, $scope.pricelistIsActive).then(function () {
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
    
    $scope.printToExcel = function () {
        var factor;

        factor = parseFloat($window.prompt('Введите фактор', 10));

        if (isNaN(factor) || factor <= 0) return;

        window.open('/pricelist/excel?id=' + $scope.item.id + '&server_id=' + $scope.server.id + '&factor=' + factor,'_blank');
    };

    $scope.displayEmptyAlert = function () {
        alert('Это пустая строка.');
    };
};