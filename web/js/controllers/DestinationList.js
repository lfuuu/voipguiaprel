var DestinationListCtrl = function($scope, Destination, Redirect, $window) {

	$scope.init = function(tab) {
		if (tab) tab.title = 'Направления';

        Destination.read({server_id: $scope.server.id}).then(function(data){
			$scope.list = data;
		});
	};

	$scope.clickCreate = function() {
		Redirect.destinationCreate().then(function () {
			$scope.init();
		});
	};

	$scope.clickItem = function(item) {
		if (window.getSelection().type == 'Range') return;

		Redirect.destinationEdit(item.id).then(function () {
			$scope.init();
		});
	}

	$scope.deleteItem = function(item)
	{
		if (!$window.confirm('Удалить?')) return;

        Destination.delete(item.id).then(function(response) {
			$scope.init()
		});
	};
};