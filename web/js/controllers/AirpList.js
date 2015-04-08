var AirpListCtrl = function($scope, Airp, Redirect, $window) {

	$scope.init = function(tab) {
		if (tab) tab.title = 'AIRP';

		Airp.read({server_id: $scope.server.id}).then(function(data){
			$scope.list = data;
		});
	};

	$scope.clickCreate = function() {
		Redirect.airpCreate().then(function () {
			$scope.init();
		});
	};

	$scope.clickItem = function(item) {
		if (window.getSelection().type == 'Range') return;

		Redirect.airpEdit(item.id).then(function () {
			$scope.init();
		});
	};

	$scope.deleteItem = function(item)
	{
		if (!$window.confirm('Удалить?')) return;

		Airp.delete(item.id).then(function(response) {
			$scope.init()
		});
	};
};