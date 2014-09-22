var RouteTableEditCtrl = function($scope, RouteTable, Outcome, params, $modalInstance, $window, Redirect) {

	if (params.id) {
		RouteTable.get({id: params.id}).then(function(data){
			$scope.item = data;
		});
	} else {
		$scope.item = {
			config_version_id: $scope.version.id,
			routes: []
		};

	}

	$scope.addRoute = function() {
		$scope.item.routes.push({ a_number_id: null, b_number_id: null, outcome_id: null });
	};

	$scope.removeRoute = function(index) {
		$scope.item.routes.splice(index, 1);
	};

	$scope.save = function()
	{
		RouteTable.save($scope.item).then(function(response) {
			$modalInstance.close();
		});
	}

	$scope.back = function()
	{
		$modalInstance.dismiss();
	}
};

