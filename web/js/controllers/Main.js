app.controller('MainCtrl', function ($rootScope, $scope, $cookies, $timeout, $modal, Redirect, Server) {
  $rootScope.server = dataServer;
  $rootScope.userName = userName;
  $rootScope.userPermissions = userPermissions;
  $rootScope.billingEnabled = billingEnabled;
  $rootScope.routingEnabled = routingEnabled;
  $rootScope.routingIsActive = (!billingEnabled || (billingEnabled && routingEnabled));
  $rootScope.billingIsActive = !$rootScope.routingIsActive;
  $rootScope.navHeight = (billingEnabled && routingEnabled) ? 100 : 65;
  $rootScope.Redirect = Redirect;

  $rootScope.tabs = [];
  $rootScope.tabsMap = {};

  $rootScope.tabs.isController = function (ctrl) {
    return $rootScope.tabs.controller == window[ctrl];
  };

  $rootScope.tabs.isTabSelected = function (tab) {
      if (tab == 'routing') {
          return $rootScope.selectedTab == tab && $rootScope.routingEnabled;
      } else if (tab == 'billing') {
          return $rootScope.selectedTab == tab && $rootScope.billingEnabled;
      }
  };

  $scope.closeErrorsPopup = function () {
    $rootScope.popupErrors = false;
  };

  var funcName = false;

  if ($cookies.selectedPage !== undefined) {
    funcName = $cookies.selectedPage;
  } else {
    for (var permissionName in $rootScope.userPermissions) {
      if (permissionName.includes('list') && permissionName !== 'user_list' && permissionName !== 'role_list' && permissionName !== 'acl_list') {
        funcName = permissionName.replace(/_([a-z])/g, function (m, w) {
          return w.toUpperCase();
        });
        break;
      }
    }
  }

  var checkServerSynchronization = function () {
    Server.checkSyncProgress({server_id: $rootScope.server.id}).then(function (data) {
      if (data) {
        $('#synchronization_in_progress_id').show();
      } else {
        $('#synchronization_in_progress_id').hide();
      }

      $timeout(checkServerSynchronization, 5000);
    });

  };

  checkServerSynchronization();

  if (funcName) {
    Redirect[funcName]();
  } else {
    Redirect.trunkList();
  }

});