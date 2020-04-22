var PricelistShortViewCtrl = function ($scope, Redirect, List, Pricelist, PricelistLocation, PricelistFilterA, PricelistFilterB, PricelistPrefixPrice, params, $modalInstance, $window) {

    $scope.locationIds = List.location();

    $scope.limit = 10;
    $scope.prefixes = [];
    
    var date = new Date();
    $scope.dateNow = date.toISOString().slice(0, 10);

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
                ((item.delta_price == '' || item.delta_price == null) ? '' : ', Наценка: ' + item.delta_price) +
                ((item.sim_partner == '' || item.sim_partner == null) ? '' : ', Sim Партнер: ' + item.sim_partner) +
                ((item.sim_profile == '' || item.sim_profile == null) ? '' : ', Sim Профиль: ' + item.sim_profile));

            var hasLocationMark = $scope.elementType === 'location' && $scope.elementId == item.id;

            $scope.list.push({
                is_location: true,
                id: item.id,
                has_location_mark: hasLocationMark,
                location_text: locationText,
                has_children: data.location[locationKey].filterA.length > 0,
                is_basic: isBasic
            });

            for (var filterAKey in data.location[locationKey].filterA) {
                var item = data.location[locationKey].filterA[filterAKey];
                var filterAName = $scope.formFilterText(item);
                var filterAId = item.id;
                var hasFilterAMark = $scope.elementType === 'filterA' && $scope.elementId == item.id;

                var hasFilterAHeader = false;
                var totalPrefixCount = 0;

                for (var filterBCountKey in data.location[locationKey].filterA[filterAKey].filterB) {
                    totalPrefixCount += $scope.countPrefix(data.location[locationKey].filterA[filterAKey].filterB[filterBCountKey].prefixPrice);
                    if (typeof data.location[locationKey].filterA[filterAKey].filterB[filterBCountKey].prefixPriceCount[0] !== 'undefined' &&
                        data.location[locationKey].filterA[filterAKey].filterB[filterBCountKey].prefixPriceCount[0].total_count > $scope.limit) {
                        totalPrefixCount++;
                    }
                }

                for (var filterBKey in data.location[locationKey].filterA[filterAKey].filterB) {
                    var item = data.location[locationKey].filterA[filterAKey].filterB[filterBKey];
                    var filterBName = $scope.formFilterText(item);
                    var filterBRating = (item.rating == 1) ? '' : item.rating;
                    var filterBUseForMinimum = item.use_for_minimum;
                    var filterBId = item.id;
                    var hasFilterBMark = $scope.elementType === 'filterB' && $scope.elementId == item.id;

                    var interconnectPrice = isNaN(parseFloat(item.interconnect_price)) ? 0 : parseFloat(item.interconnect_price);

                    var hasFilterBHeader = false;

                    var simplifiedPrefixListResult = $scope.simplifyPrefixList(item.prefixPrice);
                    var simplifiedPrefixList = simplifiedPrefixListResult.list;
                    var prefixCount = simplifiedPrefixListResult.count;
                    
                    if (typeof data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPriceCount[0] !== 'undefined' &&
                        data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPriceCount[0].total_count > $scope.limit) {
                        prefixCount++;
                    }

                    for (var prefixB in simplifiedPrefixList) {
                        var item = simplifiedPrefixList[prefixB];

                        if (!hasFilterBHeader) {
                            $scope.list.push({
                                is_filter_b_header: true,
                                is_filter_a_header: !hasFilterAHeader,
                                has_filter_a_mark: hasFilterAMark,
                                has_filter_b_mark: hasFilterBMark,
                                filter_a_name: filterAName,
                                filter_b_name: filterBName,
                                filter_b_rating: filterBRating,
                                filter_b_use_for_minimum: filterBUseForMinimum,
                                filter_a_id: filterAId,
                                filter_b_id: filterBId,
                                is_prefix_price: true,
                                prefix_b: prefixB == 'null' ? '' : prefixB,
                                prefix_count: prefixCount,
                                total_prefix_count: totalPrefixCount,
                                total_pagination_count: data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPriceCount[0].total_count,
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
                    
                    if (typeof data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPriceCount[0] !== 'undefined' &&
                        data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPriceCount[0].total_count > $scope.limit) {
                        $scope.list.push({
                            is_filter_b_header: false,
                            is_filter_a_header: false,
                            is_prefix_price: false,
                            is_prefix_price_footer: true,
                            totalCount: data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPriceCount[0].total_count,
                            currentPage: 1,
                            offset: 0,
                            filter_b_id: filterBId
                        });
                    }
                }
            }
        }

        setTimeout($scope.focusOnMark, 0);
    };
    
    $scope.simplifyPrefixList = function (data) {
        var simplifiedPrefixList = {};
        var prefixCount = 0;
        data.sort(function(a, b) {
            if ((a.prefix_b + ' ') > (b.prefix_b + ' ')) {
                return 1;
            } else if ((a.prefix_b + ' ') < (b.prefix_b + ' ')) {
                return -1;
            } else {
                if (a.date_from > b.date_from) {
                    return 1;
                } else {
                    return -1;
                }
            }
        });
        
        for (var prefixPriceKey in data) {
            var prefixItem = data[prefixPriceKey];
            var bNumberPrice = (parseFloat(prefixItem.b_number_price)).toFixed(6);
            var hasPrefixMark = $scope.elementType === 'prefix' && $scope.elementId == prefixItem.id;
            if (simplifiedPrefixList[prefixItem.prefix_b + ' '] && prefixItem.date_from >= $scope.dateNow) {
                var previousItem = simplifiedPrefixList[prefixItem.prefix_b + ' '][(simplifiedPrefixList[prefixItem.prefix_b + ' '].length - 1)];
                var previousPrice = parseFloat(previousItem.b_number_price);

                simplifiedPrefixList[prefixItem.prefix_b + ' '].push({
                    prefix_price_id: prefixItem.id,
                    has_prefix_mark: hasPrefixMark,
                    b_number_price: bNumberPrice,
                    date_from: prefixItem.date_from,
                    date_to: prefixItem.date_to,
                    price_change: previousPrice > bNumberPrice ? 'decrease' : (previousPrice == bNumberPrice ? 'none' : 'increase')
                });
            } else {
                if (!simplifiedPrefixList[prefixItem.prefix_b + ' ']) {
                    prefixCount++;
                }
                
                simplifiedPrefixList[prefixItem.prefix_b + ' '] = [{
                    prefix_price_id: prefixItem.id,
                    has_prefix_mark: hasPrefixMark,
                    b_number_price: bNumberPrice,
                    date_from: prefixItem.date_from,
                    date_to: prefixItem.date_to,
                    price_change: 'none'
                }];
            }
        }
        
        return {list: simplifiedPrefixList, count: prefixCount};
    };
    
    $scope.countPrefix = function (data) {
        var simplifiedPrefixList = {};
        var prefixCount = 0;

        for (var prefixPriceKey in data) {
            var prefixItem = data[prefixPriceKey];

            if (!simplifiedPrefixList[prefixItem.prefix_b]) {
                simplifiedPrefixList[prefixItem.prefix_b] = true;

                prefixCount++;
            }
        }
        
        return prefixCount;
    };

    $scope.focusOnMark = function () {
        if ($('[data-has-mark="true"]')[0]) {
            $('[data-has-mark="true"]')[0].scrollIntoView(true);
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
        Pricelist.getWithDependents({id: id}).then(function (data) {
            $scope.item = data;
            $scope.drawTable(data);
        });
    };

    if (params.id) {
        if (params.element_type && params.element_id) {
            $scope.elementType = params.element_type;
            $scope.elementId = params.element_id;
        }

        $scope.initData(params.id);

    } else {
        $scope.item = {};
    }

    $scope.back = function () {
        $modalInstance.dismiss();
    };
    
    $scope.setPagingData = function (page, filterBId) {
        $scope.getPrefixPriceList(filterBId, page);
    };
    
    $scope.getPrefixPriceList = function (filter_b_id, page) {
        PricelistPrefixPrice.read({pricelist_filter_b_id: filter_b_id, page_number: page}).then(function (data) {
            $scope.prefixes[filter_b_id] = data;
            
            var simplifiedPrefixResult = $scope.simplifyPrefixList(data);
            var simplifiedPrefixList = simplifiedPrefixResult.list;
            var prefixCount = simplifiedPrefixResult.count + 1;

            var index = '';

            for (var i in $scope.list) {
                if ($scope.list[i].is_prefix_price == true && $scope.list[i].filter_b_id == filter_b_id) {
                    if (index == '') {
                        var headerItem = $scope.list[i];
                        index = i;
                        break;
                    }
                }
            }
            
            for (var i in $scope.list) {
                if ($scope.list[i].is_filter_a_header == true && $scope.list[i].filter_a_id == headerItem.filter_a_id && index != i) {
                    $scope.list[i].total_prefix_count = parseInt($scope.list[i].total_prefix_count) - parseInt(headerItem.prefix_count) + parseInt(prefixCount)
                }
            }

            $scope.list.splice(index, headerItem.prefix_count);
            
            var hasFilterBHeader = false;
            var hasFilterAHeader = headerItem.is_filter_a_header;
            
            var indexCounter = 0;
            
            for (var prefixB in simplifiedPrefixList) {
                var item = simplifiedPrefixList[prefixB];
                if (!hasFilterBHeader) {
                    var toPush = {
                        is_filter_b_header: true,
                        is_filter_a_header: headerItem.is_filter_a_header,
                        has_filter_a_mark: headerItem.has_filter_a_mark,
                        has_filter_b_mark: headerItem.has_filter_b_mark,
                        filter_a_name: headerItem.filter_a_name,
                        filter_b_name: headerItem.filter_b_name,
                        filter_b_rating: headerItem.filter_b_rating,
                        filter_b_use_for_minimum: headerItem.filter_b_use_for_minimum,
                        filter_a_id: headerItem.filter_a_id,
                        filter_b_id: headerItem.filter_b_id,
                        is_prefix_price: true,
                        prefix_b: prefixB == 'null' ? '' : prefixB,
                        prefix_count: prefixCount,
                        total_prefix_count: parseInt(headerItem.total_prefix_count) - parseInt(headerItem.prefix_count) + parseInt(prefixCount),
                        total_pagination_count: headerItem.total_pagination_count,
                        interconnect_price: headerItem.interconnect_price,
                        prefixes: item
                    };
                    
                    $scope.list.splice(parseInt(index) + parseInt(indexCounter), 0, toPush);

                    hasFilterBHeader = true;
                } else {
                    var toPush = {
                        is_filter_b_header: false,
                        is_filter_a_header: !hasFilterAHeader,
                        is_prefix_price: true,
                        prefix_b: prefixB == 'null' ? '' : prefixB,
                        prefixes: item
                    };
                    $scope.list.splice(parseInt(index) + parseInt(indexCounter), 0, toPush);
                }
                
                indexCounter++;
                hasFilterAHeader = true;
            }
            
            $scope.list.splice(parseInt(index) + parseInt(indexCounter), 0, {
                is_filter_b_header: false,
                is_filter_a_header: false,
                is_prefix_price: false,
                is_prefix_price_footer: true,
                totalCount: headerItem.total_pagination_count,
                currentPage: page,
                offset: 0,
                filter_b_id: headerItem.filter_b_id
            });
        });
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
        Redirect.pricelistFilterBEdit(id, $scope.pricelistIsActive, $scope.item.date_start).then(function () {
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

    $scope.printToExcelExpanded = function () {
        window.open('/pricelist/excel?id=' + $scope.item.id, '_blank');
    };

    $scope.printToExcel = function () {
        window.open('/pricelist/excel?id=' + $scope.item.id,'_blank');
    };

    $scope.printToExcelSingleLine = function () {
        window.open('/pricelist/excel-single-line?id=' + $scope.item.id,'_blank');
    };

    $scope.printToExcelPrefixesNew = function () {
        Redirect.pricelistExcelPrefixesParamsEdit($scope.item.id);
    };

    $scope.displayEmptyAlert = function () {
        alert('Это пустая строка.');
    };
};