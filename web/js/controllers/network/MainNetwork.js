app.controller('MainNetworkCtrl', function ($rootScope, $scope, $cookies, $timeout, $modal, Redirect) {
    $rootScope.userName = userName;
    $rootScope.userId = userId;
    $rootScope.userPermissions = userPermissions;

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
    } else if ($cookies.network_selected_page !== undefined) {
        funcName = $cookies.network_selected_page;
    } else {
        // Если нет query и cookie, явно задаем funcName для раздела сети
        for (var permissionName in $rootScope.userPermissions) {
            if (permissionName === 'network_list' || permissionName === 'network_edit') {
                // Вместо преобразования, задаем явно нужное имя метода
                funcName = 'networkNode';
                break;
            }
        }
    }
    
    // Если funcName так и не установлено, задаем fallback явно
    if (!funcName) {
        funcName = 'networkNode';
    }
    
    if (funcName && id && type) {
        Redirect[funcName](id, type);
    } else if (funcName && id) {
        Redirect[funcName](id);
    } else if (funcName) {
        Redirect[funcName]();
    } else {
        Redirect.networkNode();
    }
    
});
