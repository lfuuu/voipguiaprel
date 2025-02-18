app.controller('MainNetworkCtrl', function ($rootScope, $scope, $cookies, $timeout, $modal, Redirect) {
    $rootScope.userName = userName;
    $rootScope.userId = userId;
    $rootScope.userPermissions = userPermissions;
    $rootScope.billingPermissions = billingPermissions;
    $rootScope.Redirect = Redirect;
    $rootScope.tabs = [];
    $rootScope.tabsMap = {};

    $rootScope.tabs.isController = function (ctrl) {
        return $rootScope.tabs.controller == window[ctrl];
    };

    $rootScope.tabs.isTabSelected = function (tab) {
        return $rootScope.selectedTab == tab && $rootScope.routingEnabled;
    };

    $scope.closeErrorsPopup = function () {
        $rootScope.popupErrors = false;
    };

    var funcName = false;
    var id = false;
    var type = false;

    if (query) {
        var params = query.split('&');
        funcName = params[0];
        id = params[1];
        type = params[2];
    } else if ($cookies.billing_selected_page !== undefined) {
        // Если cookie установлено, но оно может быть не актуально для сети, можно его игнорировать
        // funcName = $cookies.billing_selected_page;
    }

    // Если ни query, ни cookie не заданы, явно задаём раздел сети
    if (!funcName) {
        funcName = 'networkNode';
    }

    // Если есть query, то вызываем Redirect[funcName]
    if (query) {
        if (funcName && id && type) {
            Redirect[funcName](id, type);
        } else if (funcName && id) {
            Redirect[funcName](id);
        } else if (funcName) {
            Redirect[funcName]();
        }
    }
    // Если query отсутствует, НЕ вызываем fallback редирект, чтобы не перезагружать вкладку
    // Это позволит сохранить данные, загруженные в рамках текущей инициализации контроллера.
    // Если вам всё-таки нужен fallback, убедитесь, что он не перезаписывает данные,
    // например, проверяя текущее состояние вкладки.
});
