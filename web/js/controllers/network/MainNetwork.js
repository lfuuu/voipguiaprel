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
    } else {
        // В продакшене не используем cookie для перенаправления,
        // чтобы не затирать раздел "Сеть"
        console.log('Query string отсутствует. Не выполняем fallback редирект.');
    }

    // Если query-параметры заданы, выполняем редирект согласно ним
    if (funcName) {
        if (funcName && id && type) {
            Redirect[funcName](id, type);
        } else if (funcName && id) {
            Redirect[funcName](id);
        } else {
            Redirect[funcName]();
        }
    }
    // Если query отсутствует, ничего не делаем – оставляем текущее содержимое (раздел "Сеть")
    // Таким образом, если вы заходите напрямую на /network, будет показан основной шаблон без лишних переключений.

    // Остальная логика MainNetworkCtrl может оставаться без изменений
});
