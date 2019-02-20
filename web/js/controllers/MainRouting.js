app.controller('MainRoutingCtrl', function ($rootScope, $scope, $cookies, $timeout, $modal, Redirect) {
  $rootScope.server = dataServer;
  $rootScope.userName = userName;
  $rootScope.userId = userId;
  $rootScope.userPermissions = userPermissions;
  $rootScope.routingPermissions = routingPermissions;

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

  if ($cookies.routing_selected_page !== undefined) {
    funcName = $cookies.routing_selected_page;
  } else {
    for (var permissionName in $rootScope.userPermissions) {
      if ($rootScope.routingPermissions.indexOf(permissionName) !== -1 &&
        permissionName.includes('list') && permissionName !== 'user_list' &&
        permissionName !== 'role_list' && permissionName !== 'acl_list') {
        funcName = permissionName.replace(/_([a-z])/g, function (m, w) {
          return w.toUpperCase();
        });
        break;
      }
    }
  }

  if (funcName) {
    Redirect[funcName]();
  } else {
    Redirect.trunkList();
  }

});