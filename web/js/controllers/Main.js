app.controller('MainCtrl', function($rootScope, $scope, $timeout, $modal, Redirect) {
	$rootScope.server = dataServer;
	$rootScope.userName = userName;
	$rootScope.Redirect = Redirect;

	$rootScope.tabs = [];
	$rootScope.tabsMap = {};

	$rootScope.tabs.isController = function (ctrl){
		return $rootScope.tabs.controller == window[ctrl];
	};

	$scope.closeErrorsPopup = function() {
		$rootScope.popupErrors = false;
	};

	Redirect.trunkList();


});