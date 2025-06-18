app.controller('MainBillingServersCtrl', function ($rootScope, $scope, $cookies, $timeout, $modal, Redirect) {
  // копия MainNetworkCtrl, но для billingServers
  $rootScope.userName = userName;
  $rootScope.userId = userId;
  $rootScope.userPermissions = userPermissions;
  $rootScope.billingPermissions = billingPermissions;
  $rootScope.Redirect = Redirect;
  $rootScope.tabs = [];
  $rootScope.tabsMap = {};
  $rootScope.tabs.isController = function (ctrl) {
    return $rootScope.tabs.controller === window[ctrl];
  };
  $rootScope.tabs.isTabSelected = function (tab) {
    return $rootScope.selectedTab === tab;
  };

  $scope.closeErrorsPopup = function () {
    $rootScope.popupErrors = false;
  };

  var funcName = false, id = false, type = false;
  if (query) {
    var params = query.split('&');
    funcName = params[0]; id = params[1]; type = params[2];
  } else if ($cookies.routing_selected_page !== undefined) {
    funcName = $cookies.billing_servers_selected_page;
  } else {
    for (var p in userPermissions) {
      if (p === 'billing_servers_list' || p === 'billing_servers_edit') {
        funcName = p.replace(/_([a-z])/g, function(m,w){ return w.toUpperCase(); });
        break;
      }
    }
    if (!funcName) funcName = 'billingServers';
  }

  if (funcName && id && type)       Redirect[funcName](id, type);
  else if (funcName && id)          Redirect[funcName](id);
  else if (funcName)                Redirect[funcName]();
  else                              Redirect.billingServers();
});
