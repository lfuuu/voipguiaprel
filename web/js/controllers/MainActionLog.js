app.controller('MainActionLogCtrl', function ($rootScope, $scope, $cookies, $timeout, $modal, Redirect) {
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

  Redirect.actionLogList();
});