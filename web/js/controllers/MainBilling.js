app.controller('MainBillingCtrl', function ($rootScope, $scope, $cookies, $timeout, $modal, Redirect) {
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
        funcName = $cookies.billing_selected_page;
    } else {
        for (var permissionName in $rootScope.userPermissions) {
            if ($rootScope.billingPermissions.indexOf(permissionName) !== -1 &&
                !permissionName.includes('create') && !permissionName.includes('edit') && !permissionName.includes('delete') &&
                permissionName.includes('pricelist_list') && permissionName !== 'user_list' &&
                permissionName !== 'role_list' && permissionName !== 'acl_list') {
                funcName = permissionName.replace(/_([a-z])/g, function (m, w) {
                    return w.toUpperCase();
                });
                break;
            }
        }
    }

    if (funcName && id && type) {
        Redirect[funcName](id, type);
    } else if (funcName && id) {
        Redirect[funcName](id);
    } else if (funcName) {
        Redirect[funcName]();
    } else {
        Redirect.pricelistList();
    }

});