app.controller('MainCtrl', function($rootScope, $scope, $timeout, $modal, Redirect) {
	$rootScope.server = dataServer;
	$rootScope.userName = userName;
    $rootScope.userPermissions = userPermissions;
	$rootScope.Redirect = Redirect;

	$rootScope.tabs = [];
	$rootScope.tabsMap = {};

	$rootScope.tabs.isController = function (ctrl){
		return $rootScope.tabs.controller == window[ctrl];
	};

	$scope.closeErrorsPopup = function() {
		$rootScope.popupErrors = false;
	};

	var funcName = false;

	for (var permissionName in $rootScope.userPermissions) {
		if (permissionName.includes('list')) {
			funcName = permissionName.replace(/_([a-z])/g, function (m, w) {
                return w.toUpperCase();
            });
			break;
		}
	}

	if (funcName) {
		Redirect[funcName]();
	} else {
        Redirect.trunkList();
	}

});