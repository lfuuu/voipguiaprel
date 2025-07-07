app.controller('MainBillingServersCtrl', function ($rootScope, $scope, $cookies, $timeout, $modal, Redirect) {

    $rootScope.userName = userName;
    $rootScope.userId   = userId;
    $rootScope.userPermissions   = userPermissions;
    $rootScope.billingPermissions = billingPermissions;
    $rootScope.isBilling = true;

    $rootScope.Redirect = Redirect;

    $rootScope.tabs    = [];
    $rootScope.tabsMap = {};

    $rootScope.tabs.isController = function (ctrl) {
        return $rootScope.tabs.controller === window[ctrl];
    };

    $rootScope.tabs.isTabSelected = function (tab) {
        return $rootScope.selectedTab === tab && $rootScope.routingEnabled;
    };

    $scope.closeErrorsPopup = function () {
        $rootScope.popupErrors = false;
    };

    var funcName = false,
        id       = false,
        type     = false;

    if (query) {
        var params = query.split('&');
        funcName = params[0];
        id       = params[1];
        type     = params[2];
    } else if ($cookies.billing_servers_selected_page !== undefined) {
        funcName = $cookies.billing_servers_selected_page;
    } else {
        for (var perm in $rootScope.userPermissions) {
            if ($rootScope.billingPermissions.indexOf(perm) !== -1 &&
               (perm === 'billing_servers_list' || perm === 'billing_servers_edit')) {
                funcName = perm.replace(/_([a-z])/g, function (m, w) {
                    return w.toUpperCase();
                });
                break;
            }
        }
    }

    if (funcName && typeof Redirect[funcName] === 'function') {
        if (id && type) {
            Redirect[funcName](id, type);
        } else if (id) {
            Redirect[funcName](id);
        } else {
            Redirect[funcName]();
        }
    } else {
        Redirect.billingServers();
    }
});
