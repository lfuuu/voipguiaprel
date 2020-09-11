var PricelistShortViewCtrl = function ($scope, Redirect, List, Pricelist, PricelistLocation, PricelistFilterA, PricelistFilterB, PricelistPrefixPrice, params, $modalInstance, $window) {

    $scope.locationIds = List.location();

    $scope.limit = 10;
    $scope.prefixes = [];
    
    var date = new Date();
    $scope.dateNow = date.toISOString().slice(0, 10);

    $scope.drawTable = function (data) {
        $scope.list = data;

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

    $scope.focusOnMark = function () {
        if ($('[data-has-mark="true"]')[0]) {
            $('[data-has-mark="true"]')[0].scrollIntoView(true);
        }
    };

    $scope.initData = function (id, markType, markId) {
        Pricelist.getWithDependentsNew({id: id, mark_type: markType, mark_id: markId, type: 'short'}).then(function (data) {
            $scope.item = {};
            $scope.item.id = data[0]['id'];
            $scope.item.date_start = data[0]['date_start'];
        
            $scope.pricelistIsActive = data[0]['is_active'];
            $scope.pricelistDateStart = data[0]['date_start'];
            $scope.pricelistServiceTypeId = data[0]['service_type_id'];
            $scope.pricelistName = data[0]['name'];
            
            $scope.drawTable(data);
        });
    };

    if (params.id) {
        if (params.element_type && params.element_id) {
            $scope.initData(params.id, params.element_type, params.element_id);
        }
        
        $scope.initData(params.id, null, null);
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
        PricelistPrefixPrice.readPage({pricelist_filter_b_id: filter_b_id, page_number: page}).then(function (data) {
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
                    $scope.list[i].total_prefix_count = parseInt($scope.list[i].total_prefix_count);
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
                        total_prefix_count: parseInt(headerItem.total_prefix_count),
                        total_pagination_count: headerItem.total_pagination_count,
                        interconnect_price: headerItem.interconnect_price,
                        prefixes: item
                    };
                    
                    $scope.list.splice(parseInt(index) + parseInt(indexCounter), 0, toPush);

                    hasFilterBHeader = true;
                } else {
                    var toPush = {
                        is_filter_b_header: false,
                        filter_b_id: headerItem.filter_b_id,
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
    
    $scope.viewPrefixHistory = function (prefixB, pricelistFilterBId) {
        Redirect.pricelistSinglePrefixHistoryView(pricelistFilterBId, prefixB).then(function () {
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