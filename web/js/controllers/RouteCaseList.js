var RouteCaseListCtrl = function($scope, RouteCase, Redirect, $window) {

	$scope.init = function(tab) {
		if (tab) tab.title = 'Route cases';

		RouteCase.read({config_version_id: $scope.version.id}).then(function(data){
			$scope.list = data;
		});
	};

	$scope.clickCreate = function() {
		Redirect.routeCaseCreate().then(function () {
			$scope.init();
		});
	};

	$scope.clickItem = function(item) {
		if (window.getSelection().type == 'Range') return;

		Redirect.routeCaseEdit(item.id).then(function () {
			$scope.init();
		});
	}

	$scope.deleteItem = function(item)
	{
		if (!$window.confirm('Удалить?')) return;

		RouteCase.delete(item.id).then(function(response) {
			$scope.init()
		});
	};
};