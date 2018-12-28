var PricelistShortViewInternetCtrl = function ($scope, Redirect, List, Pricelist, PricelistLocation, PricelistFilterA, PricelistFilterB, PricelistPrefixPrice, params, $modalInstance, $window) {

    $scope.locationIds = List.location();

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
        $scope.pricelistName = data.name;
        $scope.pricelistCurrency = data.currency_id;

        for (var locationKey in data.location) {
            var item = data.location[locationKey];

            var isBasic = (item.id == $scope.item.basic_pricelist_location_id);

            var locationText = item.description ? item.description : ((isBasic ? 'Базовое местоположение: ' : 'Местоположение: ') +
                $scope.locationIds.find(function(element) {return element.id == item.location_id;}).name +
                ((item.mcc_string == '' || item.mcc_string == null) ? '' : ', MCC: ' + item.mcc_string) +
                ((item.mnc_string == '' || item.mnc_string == null) ? '' : ', MNC: ' + item.mnc_string));

            $scope.list.push({
                is_location: true,
                id: item.id,
                location_text: locationText,
                has_children: data.location[locationKey].filterA.length > 0,
                is_basic: isBasic,
                delta_price: (item.delta_price ? (item.delta_price + ' ' + $scope.pricelistCurrency + '/МБ') : ''),
                rounding_threshold: (item.rounding_threshold ? (item.rounding_threshold + ' ' + 'КБ') : '')
            });

            for (var filterAKey in data.location[locationKey].filterA) {
                for (var filterBKey in data.location[locationKey].filterA[filterAKey].filterB) {
                    var item = data.location[locationKey].filterA[filterAKey].filterB[filterBKey];

                    var simplifiedPrefixList = {};

                    for (var prefixPriceKey in data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPriceNoLimit) {
                        var prefixItem = data.location[locationKey].filterA[filterAKey].filterB[filterBKey].prefixPriceNoLimit[prefixPriceKey];
                        var bNumberPrice = parseFloat(prefixItem.b_number_price).toFixed(4);

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
                        }
                    }

                    for (var prefixB in simplifiedPrefixList) {
                        var item = simplifiedPrefixList[prefixB];

                        $scope.list.push({
                            is_prefix_price: true,
                            prefix_b: prefixB == 'null' ? '' : prefixB,
                            prefixes: item
                        });
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
        Redirect.pricelistLocationEdit(id, $scope.pricelistIsActive, 3).then(function () {
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
};