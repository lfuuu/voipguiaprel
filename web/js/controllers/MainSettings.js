app.controller('MainSettingsCtrl', function ($rootScope, $scope, $cookies, $timeout, $modal, Redirect) {
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
        return $rootScope.selectedTab == tab;
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
    } else if ($cookies.settings_selected_page !== undefined) {
        funcName = $cookies.settings_selected_page;
    } else {
        for (var permissionName in $rootScope.userPermissions) {
            if ($rootScope.settingsPermissions.indexOf(permissionName) !== -1 &&
                permissionName.includes('list') && permissionName !== 'user_list' &&
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
        Redirect.aclList();
    }
});