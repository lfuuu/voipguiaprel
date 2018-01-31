app.controller('MainCtrl', function($rootScope, $scope, $timeout, $modal, Redirect, Server) {
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
		if (permissionName.includes('list') && permissionName !== 'user_list' && permissionName !== 'role_list' && permissionName !== 'acl_list') {
			funcName = permissionName.replace(/_([a-z])/g, function (m, w) {
                return w.toUpperCase();
            });
			break;
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