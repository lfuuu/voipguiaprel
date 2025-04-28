var SmsCdrReportReadCtrl = function($rootScope, $scope, SmsCdr, List, Redirect, $window, $modal) {
    var params = $rootScope.tabs[0].params;
    $scope.sortType = 'setup_time';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.hideFilter = false;
    $scope.isLoading = false;
    $scope.noData = false;

    // Поля фильтра для SMS CDR
    $scope.filterFields = ['call_id', 'server_id', 'a_number', 'b_number', 'proto'];

    $scope.timeIntervals = List.timeInterval();

    // Инициализация фильтров по умолчанию
    $scope.initDefault = function() {
        var date = new Date();
        var dateTo = date.toISOString().slice(0, 19).replace('T', ' ');
        date.setMinutes(date.getMinutes() - 1);
        var dateFrom = date.toISOString().slice(0, 19).replace('T', ' ');
        $scope.item.time_from = dateFrom;
        $scope.item.time_to = dateTo;
        $scope.clickSearch();
    };

    // Инициализация фильтров с параметрами, если они переданы
    $scope.initWithParams = function() {
        var date = new Date();
        date.setHours(date.getHours() + 3);
        var dateTo = date.toISOString().slice(0, 19).replace('T', ' ');
        date.setDate(date.getDate() - 1);
        var dateFrom = date.toISOString().slice(0, 19).replace('T', ' ');
        if (params.object_type === 'smsc') {
            $scope.item.a_number  = params.a_number || '';
            $scope.item.b_number  = params.b_number || '';
            $scope.item.call_id   = params.call_id || '';
            $scope.item.server_id = params.server_id || '';
            $scope.item.time_from = dateFrom;
            $scope.item.time_to   = dateTo;
            $scope.item.sort_asc  = false;
            $scope.clickSearch();
        }
    };

    // Основная инициализация контроллера
    $scope.init = function(tab) {
        if (tab) { tab.title = 'Отчет по SMSC CDR'; }
        $scope.list = [];
        $scope.item = {
            call_id: '',
            server_id: '',
            limit: 100,
            a_number: '',
            b_number: '',
            proto: '',
            disconnect_cause: '',
            is_time_absolute: true,
            time_from: '',
            time_to: '',
            time_relative: '',
            sort_asc: true
        };
        if (params && params.object_type && params.object_id) {
            $scope.initWithParams();
        } else {
            $scope.initDefault();
        }
    };

    // Поиск данных по заданным фильтрам
    $scope.clickSearch = function() {
        $scope.isLoading = true;
        $scope.noData = false;
        SmsCdr.read($scope.item).then(function(data) {
            if (data.length === 0) {
                $scope.noData = true;
            }
            $scope.list = data;
            $scope.isLoading = false;
        });
    };

    List.dcOptions().then(function(data) {
        $scope.dcOptions = data;
    });
    List.protoOptions().then(function(data) {
        $scope.protoOptions = data;
    });

    // Экспорт отчета в Excel
    $scope.exportSmsCdrToExcel = function() {
        SmsCdr.ReadAndExport($scope.item).then(function() {
            $window.alert('Экспорт Excel отчета успешно выполнен!');
        });
    };

    // Существующий вызов модального окна для raw SMS через $modal.open с использованием контроллера SmsRawViewCtrl,
    // который внутри использует $http.get для запроса данных.
    $scope.openSmsRawModal = function(item) {
        $modal.open({
            templateUrl: '/templates/sms/sms_raw_view.html',
            controller: SmsRawViewCtrl,
            resolve: {
                params: function() {
                    return { smpp_cdr_id: item.id };
                }
            }
        });
    };

    // Новый вызов модального окна, который использует метод SmsCdr.raw для получения данных
    $scope.openSmsRawModalFromApi = function(item) {
        // Вызываем метод raw из SmsCdr, который вернет промис
        SmsCdr.raw({ smpp_cdr_id: item.id }).then(function(rawData) {
            // После получения данных открываем модальное окно и передаем полученные данные через resolve
            $modal.open({
                templateUrl: '/templates/sms/sms_raw_view.html',
                controller: SmsRawViewCtrl,
                resolve: {
                    params: function() {
                        // Добавляем поле rawData, чтобы в модалке можно было сразу вывести полученные данные
                        return { smpp_cdr_id: item.id, rawData: rawData };
                    }
                }
            });
        }, function(error) {
            $window.alert('Ошибка получения данных raw SMS: ' + error);
        });
    };

    $scope.openA2pSmsModal = function(item) {
        $modal.open({
            templateUrl: '/templates/sms/a2p_sms_raw_view.html',
            controller: A2pSmsRawViewCtrl,
            resolve: {
                params: function() {
                    return { cdr_id: item.id };
                }
            }
        });
    };
    
};
