var PricelistFilterBEditCtrl = function(
    $scope, $rootScope, $q, $timeout, $http,
    List, Major, PricelistFilterB, PricelistPrefixPriceHistory, Nnp,
    params, $modalInstance, $window, Redirect
) {
    $scope.NNP_MODE_FILTER = 1;
    $scope.NNP_MODE_PARAMETERS = 2;
    $scope.NNP_MODE_IMPORT = 3;
    $scope.NNP_MODE_BULK = 4; // Массовый импорт
    $scope.saveEnabled = false;
    $scope.errors = { error: false };
    $scope.round_type = [{id:1,name:'round'},{id:2,name:'ceil'}];
    $scope.porting_type = List.considerPortingMode();
    $scope.loading = false;
    $scope.saveError = false;

    var watchers = {
        filter_country: function (nv, ov) {
            if (nv !== ov) {
                Major.read({country_code: nv}).then(function (result) { $scope.filterList = result; });
            }
        },
        nnp_country: function (nv, ov) {
            if ((typeof $scope.item) == 'undefined') return;
            if (!nv || nv.length == 0 || (ov && nv.length < ov.length)) {
                $scope.item.nnp_region = null;
                $scope.item.nnp_city = null;
                $scope.item.nnp_operator = null;
                $scope.item.nnp_ndc_type = null;
                $scope.item.nnp_ndc = null;
            }
            if (JSON.stringify(nv) != JSON.stringify(ov)) {
                $scope.regionList = $scope.cityList = $scope.operatorList = $scope.ndcList = null;
            }
        },
        nnp_region: function (nv, ov) {
            if ((typeof $scope.item) == 'undefined') return;
            if (!nv || nv.length == 0 || (ov && nv.length < ov.length)) {
                $scope.item.nnp_city = null;
            }
            if (JSON.stringify(nv) != JSON.stringify(ov)) {
                $scope.cityList = null;
            }
        }
    };

    $scope.nnpDataParseError = false;
    $scope.nnpMode = $scope.NNP_MODE_PARAMETERS;
    $scope.pricelistIsActive = params.pricelist_is_active;

    var date = new Date();
    var pricelistDate = new Date(params.pricelist_date_start);

    if (params.id) {
        PricelistFilterB.get({id: params.id}).then(function (data) {
            $scope.item = data;
            $scope.item.prefixes_date_start = date > pricelistDate ? date.toISOString().slice(0,10) : pricelistDate.toISOString().slice(0,10);
            $scope.item.prefixes_date_end = '3000-01-01';
            $scope.item.prefixes_replace = false;
            $scope.setNnpFields();
            if (data.nnp_filter && data.filter_country) {
                Major.read({country_code: data.filter_country}).then(function (result) { $scope.filterList = result; });
            }
            $scope.$watch('item.nnp_region', watchers.nnp_region);
            $scope.$watch('item.filter_country', watchers.filter_country);
        });
    } else if (params.filter_a_id) {
        $scope.item = {
            pricelist_filter_a_id: params.filter_a_id,
            nnp_destination: null, nnp_country: null, nnp_city: null, nnp_region: null, nnp_operator: null, nnp_ndc_type: null, nnp_ndc: null,
            mode_selected: true,
            interconnect_price: 0, ported_num_price: 0, operator_price: 0, transit_price: 0,
            tarification_free_seconds: params.pricelist_default_tarification_free_seconds,
            tarification_interval_seconds: params.pricelist_default_tarification_interval_seconds,
            tarification_type: params.pricelist_default_tarification_type,
            tarification_min_paid_seconds: params.pricelist_default_tarification_min_paid_seconds,
            filter_country: 643, rating: 1, use_for_minimum: false, use_cutoff_for_minimum: false,
            prefixes_date_start: date > pricelistDate ? date.toISOString().slice(0,10) : pricelistDate.toISOString().slice(0,10),
            prefixes_date_end: '3000-01-01', prefixes_replace: false, consider_porting_mode: 1
        };
        $scope.saveEnabled = true;
        $scope.$watch('item.nnp_region', watchers.nnp_region);
        $scope.$watch('item.filter_country', watchers.filter_country);
    } else {
        $scope.item = {
            nnp_destination: null, nnp_country: null, nnp_city: null, nnp_region: null, nnp_operator: null, nnp_ndc_type: null, nnp_ndc: null,
            mode_selected: true,
            interconnect_price: 0, ported_num_price: 0, operator_price: 0, transit_price: 0,
            tarification_free_seconds: 0, tarification_interval_seconds: 60, tarification_type: 2, tarification_min_paid_seconds: 0,
            filter_country: 643, rating: 1, use_for_minimum: false, use_cutoff_for_minimum: false,
            prefixes_date_start: date > pricelistDate ? date.toISOString().slice(0,10) : pricelistDate.toISOString().slice(0,10),
            prefixes_date_end: '3000-01-01', prefixes_replace: false, consider_porting_mode: 1
        };
        $scope.saveEnabled = true;
        $scope.$watch('item.nnp_region', watchers.nnp_region);
        $scope.$watch('item.filter_country', watchers.filter_country);
    }

    $scope.setNnpMode = function(nnpMode) { $scope.nnpMode = nnpMode; };

    Nnp.countryList().then(function (data) {
        $scope.countryList = data;
        countryLoadComplete = true;
        $scope.$watch('item.nnp_country', watchers.nnp_country);
    });
    Nnp.ndcTypeList().then(function (data) { $scope.ndcTypeList = data; });

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
        $scope.loading = true; $scope.saveError = false; $scope.errors = [];
        var data = angular.copy($scope.item);
        data.nnp_country  = $scope.stringifyNnpData(data.nnp_country);
        data.nnp_region   = $scope.stringifyNnpData(data.nnp_region);
        data.nnp_city     = $scope.stringifyNnpData(data.nnp_city);
        data.nnp_operator = $scope.stringifyNnpData(data.nnp_operator);
        data.nnp_ndc_type = $scope.stringifyNnpData(data.nnp_ndc_type);
        data.nnp_ndc      = $scope.stringifyNnpData(data.nnp_ndc);

        PricelistFilterB.save(data).then(function (result) {
            $scope.loading = false;
            if (result.error) { $scope.displayError(result); return; }
            if (typeof result.pricelist_filter_b_id !== 'undefined' &&
                typeof result.import_key          !== 'undefined' &&
                typeof result.is_replace          !== 'undefined') {
                window.open('/importer.php?id=' + result.pricelist_filter_b_id + '&key=' + result.import_key + '&is_replace=' + result.is_replace, '_blank');
            }
            $modalInstance.close();
        }).catch(function () {
            $scope.loading = false; $scope.saveError = true;
        });
    };

    $scope.displayError = function(resp) { $scope.errors[resp.field + '_error'] = resp.error; };

    $scope.setNnpFields = function() {
        try {
            $scope.item.nnp_ndc_type = $scope.parseNnpData($scope.item.nnp_ndc_type);
            if ($scope.item.nnp_filter != '') { $scope.nnpMode = $scope.NNP_MODE_FILTER; }
            if ($scope.item.nnp_country !== '{}') {
                $scope.item.nnp_country  = $scope.parseNnpData($scope.item.nnp_country);
                $scope.item.nnp_region   = $scope.parseNnpData($scope.item.nnp_region);
                $scope.item.nnp_operator = $scope.parseNnpData($scope.item.nnp_operator);
                $scope.item.nnp_city     = $scope.parseNnpData($scope.item.nnp_city);
                $scope.item.nnp_ndc      = $scope.parseNnpData($scope.item.nnp_ndc);
            } else {
                $scope.saveEnabled = true;
            }
        } catch (e) { $scope.nnpDataParseError = true; }
    };

    $scope.loadRegions = function () {
        if ($scope.item.nnp_country && $scope.item.nnp_country.length > 0) {
            Nnp.regionList({country_code: $scope.item.nnp_country}).then(function (data) {
                $scope.regionList = data; $scope.item.nnp_region = $scope.parseNnpData($scope.item.nnp_region);
            });
        }
    };
    $scope.loadOperators = function () {
        if ($scope.item.nnp_country && $scope.item.nnp_country.length > 0) {
            Nnp.operatorList({country_code: $scope.item.nnp_country}).then(function (data) {
                $scope.operatorList = data; $scope.item.nnp_operator = $scope.parseNnpData($scope.item.nnp_operator);
            });
        }
    };
    $scope.loadCities = function () {
        if ($scope.item.nnp_region && $scope.item.nnp_region.length > 0) {
            Nnp.cityList({ country_code: $scope.item.nnp_country, region: $scope.item.nnp_region })
                .then(function (data) {
                    $scope.cityList = data; $scope.item.nnp_city = $scope.parseNnpData($scope.item.nnp_city);
                });
        }
    };
    $scope.loadNdcs = function () {
        if ($scope.item.nnp_country && $scope.item.nnp_country.length > 0) {
            Nnp.ndcList({country_code: $scope.item.nnp_country}).then(function (data) {
                $scope.ndcList = data; $scope.item.nnp_ndc = $scope.parseNnpData($scope.item.nnp_ndc);
            });
        }
    };

    $scope.saveAndUpdate = function() {
        var data = angular.copy($scope.item);
        data.nnp_country  = $scope.stringifyNnpData(data.nnp_country);
        data.nnp_region   = $scope.stringifyNnpData(data.nnp_region);
        data.nnp_city     = $scope.stringifyNnpData(data.nnp_city);
        data.nnp_operator = $scope.stringifyNnpData(data.nnp_operator);
        data.nnp_ndc_type = $scope.stringifyNnpData(data.nnp_ndc_type);
        data.nnp_ndc      = $scope.stringifyNnpData(data.nnp_ndc);
        PricelistFilterB.saveAndUpdate(data).then(function() { $modalInstance.close(); });
    };

    $scope.openPrefixPriceHistory = function(item) {
        Redirect.pricelistPrefixPriceHistoryView(item.id).then(function () { $scope.init(); });
    };
    $scope.undoPrefixImport = function(item) {
        if (!$window.confirm("Вы уверены, что хотите отменить эту операцию?\n" +
            "Будут отменены ВСЕ действия, произведённые с прайсами префиксов\n" +
            "выбранного в данный момент фильтра Б!")) return;
        window.open('/resetter.php?id=' + item.id, '_blank');
    };
    $scope.back = function () { $modalInstance.close(); };
    $scope.deleteHistoryItem = function(item) {
    if (!item || !item.id) { 
        alert("Невозможно удалить: некорректный элемент."); 
        return; 
    }
    var ok = $window.confirm(
        "Удалить запись истории с ID " + item.id + 
        " и все связанные префиксы?\nЭто действие необратимо!"
    );
    if (ok) {
        PricelistFilterB.deleteHistoryItem(item.id).then(function(response) {
            if (response.status === 'success') {
                alert("Удалено: " + response.deleted_prefixes + " префиксов\n" + response.message);
            }
        }, function(error) {
            alert("Произошла ошибка при удалении: " + error.message);
        });
    }
};


    // =========================
    // Массовый импорт — предпросмотр (через $http)
    // =========================
    $scope.bulk = { rows: '', replace: false, dry_run: true, loadingFile: false };
    $scope.bulkLoading = false;
    $scope.bulkResult = null;
    $scope.bulkSummary = null;

    $scope.triggerXlsxImport = function () {
        var input = document.getElementById('pfb-bulk-xlsx-file');
        if (!input) return;

        if (!input._pfbBound) {
            input.addEventListener('change', function (evt) {
                $scope.onXlsxFileChange(evt);
            });
            input._pfbBound = true;
        }

        $timeout(function(){ input.click(); }, 0, false);
    };

    $scope.onXlsxFileChange = function (evt) {
        var target = evt && evt.target;
        var files = target && target.files;
        if (!files || !files.length) return;

        var file = files[0];
        if (target) target.value = '';

        $scope.$applyAsync(function () {
            $scope.bulk.loadingFile = true;
            $scope.bulkResult = null;
        });

        var reader = new FileReader();
        reader.onload = function (e) {
            var base64 = e.target.result;

            PricelistFilterB.parseXlsx(base64).then(function (res) {
                var data = (res && res.data) ? res.data : res;
                $scope.bulk.rows = data.rows_text || '';

                var issues = data.issues || [];
                if (issues.length) {
                    $scope.bulkResult = {
                        preview: [],
                        errors: issues.map(function (i) {
                            return { line: i.row || '-', message: i.message || i };
                        })
                    };
                }

                if ($scope.bulk.rows) {
                    // сразу запустить проверку
                    $scope.bulkCheck();
                }
            }, function (err) {
                var msg =
                    (err && err.data && (err.data.message || err.data.error)) ||
                    (err && err.statusText) ||
                    'Не удалось разобрать XLSX';
                $scope.bulkResult = {
                    preview: [],
                    errors: [{ line: '-', message: msg }]
                };
            }).finally(function () {
                $scope.bulk.loadingFile = false;
            });
        };
        reader.onerror = function () {
            $scope.$applyAsync(function () {
                $scope.bulk.loadingFile = false;
                $scope.bulkResult = { preview: [], errors: [{ line: '-', message: 'Ошибка чтения файла' }] };
            });
        };

        reader.readAsDataURL(file);
    };

    function normalize(resData) {
        // сервер всегда присылает объект — просто подстрахуемся
        var obj = (resData && typeof resData === 'object') ? resData : {};
        if (!Array.isArray(obj.preview)) obj.preview = [];
        if (!Array.isArray(obj.errors))  obj.errors  = [];
        return obj;
    }
    function computeBulkSummary(obj) {
        // если сервер прислал summary — используем его
        if (obj && obj.summary) return obj.summary;
        return 'Обработано ' + (obj && obj.preview ? obj.preview.length : 0) + ' строк';
    }
    function assignBulkFromHttp(res) {
        var data = normalize(res && res.data ? res.data : res);
        $scope.bulkResult  = data;
        $scope.bulkSummary = computeBulkSummary(data);
    }

    $scope.getPreview = function(){ return ($scope.bulkResult && $scope.bulkResult.preview) || []; };

    $scope.bulkPayload = function(dry) {
        return {
            pricelist_filter_a_id: $scope.item.pricelist_filter_a_id || params.filter_a_id,
            template: angular.copy($scope.item),
            rows: $scope.bulk.rows,
            delimiter: 'auto',
            replace: !!$scope.bulk.replace,
            dry_run: !!dry
        };
    };

    var BULK_URL = '/json/pricelist-filter-b/bulk-import';

    $scope.bulkCheck = function () {
        if (!$scope.bulk.rows) return;
        $scope.nnpMode = $scope.NNP_MODE_BULK;
        $scope.bulkLoading = true;

        $http.post(BULK_URL, $scope.bulkPayload(true))
            .then(assignBulkFromHttp, assignBulkFromHttp) // показываем даже при ok:false / HTTP-ошибке
            .finally(function(){ $scope.bulkLoading = false; });
    };

    $scope.bulkImport = function () {
        if (!$scope.bulk.rows) return;
        $scope.nnpMode = $scope.NNP_MODE_BULK;
        $scope.bulkLoading = true;

        $http.post(BULK_URL, $scope.bulkPayload(false))
            .then(function(res){
                assignBulkFromHttp(res);
                if ($scope.bulkResult.ok && !$scope.bulkResult.dry_run) $modalInstance.close();
            }, assignBulkFromHttp)
            .finally(function(){ $scope.bulkLoading = false; });
    };
};
