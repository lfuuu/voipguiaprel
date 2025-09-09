var PricelistFilterBEditCtrl = function($scope, $rootScope, $q, List, Major, PricelistFilterB, PricelistPrefixPriceHistory, Nnp, params, $modalInstance, $window, Redirect) {

    $scope.NNP_MODE_FILTER = 1;
    $scope.NNP_MODE_PARAMETERS = 2;
    $scope.NNP_MODE_IMPORT = 3;
    $scope.NNP_MODE_BULK = 4; // новая вкладка: Массовый импорт

    $scope.saveEnabled = false;
    $scope.errors = { error: false };

    $scope.round_type = [
        {id: 1, name: 'round'},
        {id: 2, name: 'ceil'}
    ];
    $scope.porting_type = List.considerPortingMode();
    $scope.loading = false;
    $scope.saveError = false;

    var watchers = {
        filter_country: function (newValue, oldValue) {
            if (newValue !== oldValue) {
                Major.read({country_code: newValue}).then(function (result) {
                    $scope.filterList = result;
                });
            }
        },
        nnp_country: function (newValue, oldValue) {
            if ((typeof $scope.item) == 'undefined') return;

            if (!newValue || newValue.length == 0 || (oldValue && newValue.length < oldValue.length)) {
                $scope.item.nnp_region = null;
                $scope.item.nnp_city = null;
                $scope.item.nnp_operator = null;
                $scope.item.nnp_ndc_type = null;
                $scope.item.nnp_ndc = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.regionList = null;
                $scope.cityList = null;
                $scope.operatorList = null;
                $scope.ndcList = null;
            }
        },
        nnp_region: function (newValue, oldValue) {
            if ((typeof $scope.item) == 'undefined') return;

            if (!newValue || newValue.length == 0 || (oldValue && newValue.length < oldValue.length)) {
                $scope.item.nnp_city = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.cityList = null;
            }
        }
    };

    $scope.nnpDataParseError = false;
    $scope.nnpProcessed = false;
    $scope.nnpProcessFailed = false;
    $scope.nnpProcessComplete = false;

    $scope.nnpMode = $scope.NNP_MODE_PARAMETERS;

    $scope.pricelistIsActive = params.pricelist_is_active;

    var date = new Date();
    var pricelistDate = new Date(params.pricelist_date_start);

    if (params.id) {
        PricelistFilterB.get({id: params.id}).then(function (data) {
            $scope.item = data;

            $scope.item.prefixes_date_start = date > pricelistDate ? date.toISOString().slice(0, 10) : pricelistDate.toISOString().slice(0, 10);
            $scope.item.prefixes_date_end = '3000-01-01';
            $scope.item.prefixes_replace = false;

            $scope.setNnpFields();

            if (data.nnp_filter && data.filter_country) {
                Major.read({country_code: data.filter_country}).then(function (result) {
                    $scope.filterList = result;
                });
            }

            $scope.$watch('item.nnp_region', watchers.nnp_region);
            $scope.$watch('item.filter_country', watchers.filter_country);
        });
    } else if (params.filter_a_id) {
        $scope.item = {
            pricelist_filter_a_id: params.filter_a_id,
            nnp_destination: null,
            nnp_country: null,
            nnp_city: null,
            nnp_region: null,
            nnp_operator: null,
            nnp_ndc_type: null,
            nnp_ndc: null,
            mode_selected: true,
            interconnect_price: 0,
            ported_num_price: 0,
            operator_price: 0,
            transit_price: 0,
            tarification_free_seconds: params.pricelist_default_tarification_free_seconds,
            tarification_interval_seconds: params.pricelist_default_tarification_interval_seconds,
            tarification_type: params.pricelist_default_tarification_type,
            tarification_min_paid_seconds: params.pricelist_default_tarification_min_paid_seconds,
            filter_country: 643,
            rating: 1,
            use_for_minimum: false,
            use_cutoff_for_minimum: false,
            prefixes_date_start: date > pricelistDate ? date.toISOString().slice(0, 10) : pricelistDate.toISOString().slice(0, 10),
            prefixes_date_end: '3000-01-01',
            prefixes_replace: false,
            consider_porting_mode: 1
        };

        $scope.saveEnabled = true;

        $scope.$watch('item.nnp_region', watchers.nnp_region);
        $scope.$watch('item.filter_country', watchers.filter_country);
    } else {
        $scope.item = {
            nnp_destination: null,
            nnp_country: null,
            nnp_city: null,
            nnp_region: null,
            nnp_operator: null,
            nnp_ndc_type: null,
            nnp_ndc: null,
            mode_selected: true,
            interconnect_price: 0,
            ported_num_price: 0,
            operator_price: 0,
            transit_price: 0,
            tarification_free_seconds: 0,
            tarification_interval_seconds: 60,
            tarification_type: 2,
            tarification_min_paid_seconds: 0,
            filter_country: 643,
            rating: 1,
            use_for_minimum: false,
            use_cutoff_for_minimum: false,
            prefixes_date_start: date > pricelistDate ? date.toISOString().slice(0, 10) : pricelistDate.toISOString().slice(0, 10),
            prefixes_date_end: '3000-01-01',
            prefixes_replace: false,
            consider_porting_mode: 1
        };

        $scope.saveEnabled = true;

        $scope.$watch('item.nnp_region', watchers.nnp_region);
        $scope.$watch('item.filter_country', watchers.filter_country);
    }

    $scope.setNnpMode = function(nnpMode) {
        $scope.nnpMode = nnpMode;
    };

    Nnp.countryList().then(function (data) {
        $scope.countryList = data;
        countryLoadComplete = true;
        $scope.$watch('item.nnp_country', watchers.nnp_country);
    });

    Nnp.ndcTypeList().then(function (data) {
        $scope.ndcTypeList = data;
    });

    $scope.parseNnpData = function (data) {
        if (!data) return [];
        if ((typeof data) != 'string') return data;
        return data.replace('{', '').replace('}', '').split(',');
    };

    $scope.stringifyNnpData = function (data) {
        if (!data || data == '{}') return '{}';
        if (((typeof data) == 'string') && data.charAt(0) == '{') return data;
        return '{' + data.join(',') + '}';
    };

    $scope.save = function () {
        $scope.loading = true;
        $scope.saveError = false;
        $scope.errors = [];
        var data = angular.copy($scope.item);

        data.nnp_country = $scope.stringifyNnpData(data.nnp_country);
        data.nnp_region = $scope.stringifyNnpData(data.nnp_region);
        data.nnp_city = $scope.stringifyNnpData(data.nnp_city);
        data.nnp_operator = $scope.stringifyNnpData(data.nnp_operator);
        data.nnp_ndc_type = $scope.stringifyNnpData(data.nnp_ndc_type);
        data.nnp_ndc = $scope.stringifyNnpData(data.nnp_ndc);

        PricelistFilterB.save(data).then(
            function (result) {
                $scope.loading = false;
                if (result.error) {
                    $scope.displayError(result);
                    return;
                } else {
                    if (
                        (typeof result.pricelist_filter_b_id) !== 'undefined' &&
                        (typeof result.import_key) !== 'undefined' &&
                        (typeof result.is_replace) !== 'undefined'
                    ) {
                        window.open('/importer.php?id=' + result.pricelist_filter_b_id + '&key=' + result.import_key + '&is_replace=' + result.is_replace, '_blank');
                    }
                    $modalInstance.close();
                }
            }
        ).catch(function () {
            $scope.loading = false;
            $scope.saveError = true;
        });
    };

    $scope.displayError = function(response) {
        $scope.errors[response.field + '_error'] = response.error;
    };

    $scope.setNnpFields = function() {
        try {
            $scope.item.nnp_ndc_type = $scope.parseNnpData($scope.item.nnp_ndc_type);

            if ($scope.item.nnp_filter != '') {
                $scope.nnpMode = $scope.NNP_MODE_FILTER;
            }

            if ($scope.item.nnp_country !== '{}') {
                $scope.item.nnp_country = $scope.parseNnpData($scope.item.nnp_country);
                $scope.item.nnp_region = $scope.parseNnpData($scope.item.nnp_region);
                $scope.item.nnp_operator = $scope.parseNnpData($scope.item.nnp_operator);
                $scope.item.nnp_city = $scope.parseNnpData($scope.item.nnp_city);
                $scope.item.nnp_ndc = $scope.parseNnpData($scope.item.nnp_ndc);
            } else {
                $scope.saveEnabled = true;
            }
        } catch (error) {
            $scope.nnpDataParseError = true;
        }
    };

    $scope.loadRegions = function () {
        if ($scope.item.nnp_country && $scope.item.nnp_country.length > 0) {
            Nnp.regionList({country_code: $scope.item.nnp_country}).then(function (data) {
                $scope.regionList = data;
                $scope.item.nnp_region = $scope.parseNnpData($scope.item.nnp_region);
            });
        }
    };
    $scope.loadOperators = function () {
        if ($scope.item.nnp_country && $scope.item.nnp_country.length > 0) {
            Nnp.operatorList({country_code: $scope.item.nnp_country}).then(function (data) {
                $scope.operatorList = data;
                $scope.item.nnp_operator = $scope.parseNnpData($scope.item.nnp_operator);
            });
        }
    };
    $scope.loadCities = function () {
        if ($scope.item.nnp_region && $scope.item.nnp_region.length > 0) {
            Nnp.cityList({
                country_code: $scope.item.nnp_country,
                region: $scope.item.nnp_region
            }).then(function (data) {
                $scope.cityList = data;
                $scope.item.nnp_city = $scope.parseNnpData($scope.item.nnp_city);
            });
        }
    };
    $scope.loadNdcs = function () {
        if ($scope.item.nnp_country && $scope.item.nnp_country.length > 0) {
            Nnp.ndcList({country_code: $scope.item.nnp_country}).then(function (data) {
                $scope.ndcList = data;
                $scope.item.nnp_ndc = $scope.parseNnpData($scope.item.nnp_ndc);
            });
        }
    };

    $scope.saveAndUpdate = function() {
        var data = angular.copy($scope.item);

        data.nnp_country = $scope.stringifyNnpData(data.nnp_country);
        data.nnp_region = $scope.stringifyNnpData(data.nnp_region);
        data.nnp_city = $scope.stringifyNnpData(data.nnp_city);
        data.nnp_operator = $scope.stringifyNnpData(data.nnp_operator);
        data.nnp_ndc_type = $scope.stringifyNnpData(data.nnp_ndc_type);
        data.nnp_ndc = $scope.stringifyNnpData(data.nnp_ndc);

        PricelistFilterB.saveAndUpdate(data).then(function() {
            $modalInstance.close();
        });
    };

    $scope.openPrefixPriceHistory = function(item) {
        Redirect.pricelistPrefixPriceHistoryView(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.undoPrefixImport = function(item) {
        if (!$window.confirm("Вы уверены, что хотите отменить эту операцию?\n" +
            "Будут отменены ВСЕ действия, произведенные с прайсами префиксов\n" +
            "выбранного в данный момент фильтра Б!")) return;
        window.open('/resetter.php?id=' + item.id, '_blank');
    };

    $scope.back = function () {
        $modalInstance.close();
    };

    $scope.deleteHistoryItem = function(item) {
        if (!item || !item.id) {
            alert("Невозможно удалить: некорректный элемент.");
            return;
        }
        var isConfirmed = $window.confirm("Вы уверены, что хотите удалить эту запись истории с ID " + item.id + "?");
        if (isConfirmed) {
            PricelistFilterB.deleteHistoryItem(item.id).then(function(response) {
                if (response.status === 'success') {
                    alert(response.message);
                }
            }, function(error) {
                alert("Произошла ошибка при удалении: " + error.message);
            });
        }
    };

    // =========================
    // Массовый импорт (новое)
    // =========================
    $scope.bulk = { rows: '', replace: false, dry_run: true };
    $scope.bulkLoading = false;
    $scope.bulkResult = null;
    $scope.bulkSummary = null;

    function computeBulkSummary(res) {
    var previewLen = (res && res.preview && res.preview.length) ? res.preview.length : 0;
    // если сервер дал summary — используем его, иначе fallback
    var base = (res && res.summary) ? res.summary : ('Обработано ' + previewLen + ' строк');
    // для dry-run можно подсветить режим замены по состоянию чекбокса
    if (res && res.dry_run && $scope.bulk && $scope.bulk.replace) {
        base += ' (режим полной замены фильтров B)';
    }
    return base;
    }

    $scope.bulkPayload = function(dry) {
        return {
            pricelist_filter_a_id: $scope.item.pricelist_filter_a_id || params.filter_a_id,
            template: angular.copy($scope.item), // используем текущие настройки модалки как шаблон
            rows: $scope.bulk.rows,
            delimiter: 'auto',
            replace: !!$scope.bulk.replace,
            dry_run: !!dry
        };
    };

    $scope.bulkCheck = function () {
  if (!$scope.bulk.rows) return;
  $scope.nnpMode = $scope.NNP_MODE_BULK;
  $scope.bulkLoading = true;
  PricelistFilterB.bulkImport($scope.bulkPayload(true))
    .then(function (res) {
      $scope.bulkResult  = res;
      $scope.bulkSummary = computeBulkSummary(res);   // ← вот это добавили
    })
    ["finally"](function () { $scope.bulkLoading = false; });
};


    $scope.bulkImport = function () {
  if (!$scope.bulk.rows) return;
  $scope.nnpMode = $scope.NNP_MODE_BULK;
  $scope.bulkLoading = true;
  PricelistFilterB.bulkImport($scope.bulkPayload(false))
    .then(function (res) {
      $scope.bulkResult  = res;
      $scope.bulkSummary = computeBulkSummary(res);   // ← и здесь тоже
      if (res.ok && !res.dry_run) $modalInstance.close();
    })
    ["finally"](function () { $scope.bulkLoading = false; });
};
};
