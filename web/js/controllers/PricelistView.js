var PricelistViewCtrl = function ($scope, Redirect, List, Pricelist, PricelistLocation, PricelistFilterA, PricelistFilterB, PricelistPrefixPrice, params, $modalInstance, $window) {

    $scope.locationIds = List.location();

    $scope.limit = 10;
    $scope.prefixes = [];

    $scope.drawTable = function (data) {
        $scope.list = data;
        $scope.item.id = data[2].id;
        
        $scope.pricelistIsActive = data[2].is_active;
        $scope.pricelistDateStart = data[2].date_start;
        $scope.pricelistDefaultTarificationFreeSeconds = data[2].default_tarification_free_seconds;
        $scope.pricelistDefaultTarificationIntervalSeconds = data[2].default_tarification_interval_seconds;
        $scope.pricelistDefaultTarificationMinPaidSeconds = data[2].default_tarification_min_paid_seconds;
        $scope.pricelistServiceTypeId = data[2].service_type_id;
        $scope.pricelistDefaultTarificationType = data[2].default_tarification_type;
    };

    $scope.initData = function (id) {
        Pricelist.getWithDependentsNew({id: id}).then(function (data) {
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
            $scope.pricelistDefaultTarificationType,
            $scope.item[2].date_start
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
        Redirect.pricelistFilterAEdit(id, $scope.pricelistIsActive).then(function () {
            $scope.initData($scope.item.id);
        }, function () {
            $scope.initData($scope.item.id);
        });
    };

    $scope.editFilterB = function (id) {
        Redirect.pricelistFilterBEdit(id, $scope.pricelistIsActive, $scope.item[2].date_start).then(function () {
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

    $scope.deleteFilterB = function (item) {
        var message = '';

        if (item.has_children) {
            message = 'Вы уверены, что хотите удалить фильтр B и ВСЕ созданные в нем прайсы префиксов?';
        } else {
            message = 'Удалить фильтр B?';
        }

        if (!$window.confirm(message)) return;

        PricelistFilterB.delete(item.id).then(function(response) {
            $scope.initData($scope.item.id);
        });
    };

    $scope.searchPrefixById = function(searchValue, filterBId) {
        if (!searchValue) {
            alert('Пожалуйста, введите значение prefix_b для поиска.');
            return;
        }
    
        var requestData = { pricelist_filter_b_id: filterBId };
        
        PricelistPrefixPrice.readAll(requestData).then(function(data) {
            var index = data.findIndex(function(item) {
                return item.prefix_b && item.prefix_b.trim() === searchValue.trim();
            });
    
            if (index !== -1) {
                var pageNumber = Math.floor(index / $scope.limit) + 1;
    
                // Обновляем currentPage для конкретного item
                $scope.list.forEach(function(item) {
                    if(item.filter_b_id === filterBId) {
                        item.currentPage = pageNumber;
                    }
                });
    
                // Обновляем пагинацию и данные на странице
                $scope.setPagingData(pageNumber, filterBId);
                
                // Применяем изменения
                if (!$scope.$$phase) $scope.$apply();
            } else {
                alert('Префикс с таким значением prefix_b не найден.');
            }
        }).catch(function(error) {
            console.error("Произошла ошибка при поиске:", error);
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