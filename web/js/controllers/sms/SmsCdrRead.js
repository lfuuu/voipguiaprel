/* controllers/sms/sms_cdr_new.js */
var SmsCdrReportReadCtrl = function ($rootScope, $scope, SmsCdr, List, Redirect, $window, $modal) {

    /* ------------------------------------------------------------------ */
    /* 1.  Базовые переменные / сортировка / флаги                         */
    /* ------------------------------------------------------------------ */
    var params           = $rootScope.tabs[0].params || {};

    $scope.sortType      = 'dt_create';
    $scope.sortReverse   = false;
    $scope.searchQuery   = '';

    $scope.hideFilter    = false;
    $scope.isLoading     = false;
    $scope.noData        = false;

    $scope.filterFields = [
        'sessionid','server_id','msisdn','destination',
        'mcc','mnc','imsi','type','direction','quantity','timestamp'
      ];
      
    List.mccOptions().then(function(d){ $scope.mccOptions = d; });
    List.mncOptions().then(function(d){ $scope.mncOptions = d; });
      
      

    $scope.timeIntervals = List.timeInterval();          /* «Последние 5 мин» и т. д. */

    /* ------------------------------------------------------------------ */
    /* 2.  Поиск данных по фильтрам                                       */
    /* ------------------------------------------------------------------ */
    $scope.clickSearch = function () {
        $scope.isLoading = true;
        $scope.noData    = false;

        SmsCdr.read($scope.item).then(function (data) {
            $scope.list      = data;
            $scope.noData    = data.length === 0;
            $scope.isLoading = false;
        }, function (err) {
            $scope.isLoading = false;
            $window.alert('Ошибка запроса: ' + err);
        });
    };

    /* ------------------------------------------------------------------ */
    /* 3.  Инициализация                                                   */
    /* ------------------------------------------------------------------ */
    /* Заполнить фильтры значениями «по умолчанию»: последние 60 с          */
    $scope.initDefault = function () {
        var dTo   = new Date();
        var dFrom = new Date(dTo.getTime() - 60 * 1000);    /* минута назад */

        $scope.item.time_from = dFrom.toISOString().slice(0,19).replace('T',' ');
        $scope.item.time_to   = dTo  .toISOString().slice(0,19).replace('T',' ');

        $scope.clickSearch();
    };

    /* Если вкладка открыта из другого места и пришли параметры —          */
    /* подставляем их и сразу ищем                                         */
    $scope.initWithParams = function () {
        var dTo   = new Date();           /* +3 ч не требуется — dt_create UTC-neutral */
        var dFrom = new Date(dTo.getTime() - 24*60*60*1000);   /* сутки назад */

        $scope.item.sessionid   = params.sessionid   || '';
        $scope.item.msisdn      = params.msisdn      || '';
        $scope.item.destination = params.destination || '';
        $scope.item.server_id   = params.server_id   || '';

        $scope.item.time_from   = dFrom.toISOString().slice(0,19).replace('T',' ');
        $scope.item.time_to     = dTo  .toISOString().slice(0,19).replace('T',' ');
        $scope.item.sort_asc    = false;

        $scope.clickSearch();
    };

    /* ------------------------------------------------------------------ */
    /* 4.  Первичная сборка item-объекта и запуск                          */
    /* ------------------------------------------------------------------ */
    $scope.init = function (tab) {
        if (tab) { tab.title = 'Отчёт по SMS CDR (новый)'; }

        $scope.list = [];
        $scope.item = {
            sessionid        : '',
            server_id        : '',
            limit            : 100,
            msisdn           : '',
            destination      : '',
            direction        : '',
            is_time_absolute : true,
            time_from        : '',
            time_to          : '',
            time_relative    : '',
            sort_asc         : true
        };

        if (params && params.object_type && params.object_id) {
            $scope.initWithParams();
        } else {
            $scope.initDefault();
        }
    };

    /* ------------------------------------------------------------------ */
    /* 5.  Экспорт в Excel                                                */
    /* ------------------------------------------------------------------ */
    $scope.exportSmsCdrToExcel = function () {
        SmsCdr.ReadAndExport($scope.item).then(function () {
            $window.alert('Экспорт Excel-отчёта выполнен.');
        });
    };

    /* ------------------------------------------------------------------ */
    /* 6.  Модальные окна raw                                             */
    /* ------------------------------------------------------------------ */
    $scope.openSmsRawModal = function (item) {
        $modal.open({
            templateUrl : '/templates/sms/sms_raw_view.html',
            controller  : SmsRawViewCtrl,
            resolve     : {
                params : function () {
                    return { cdr_id: item.id };   /* поле id новой таблицы */
                }
            }
        });
    };

    /* 7.  Старт */
    $scope.init($rootScope.tabs[$rootScope.tabs.length - 1]);
};
