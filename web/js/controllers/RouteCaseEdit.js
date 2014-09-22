var RouteCaseEditCtrl = function($scope, RouteCase, params, $modalInstance, $window) {

	if (params.id) {
		RouteCase.get({id: params.id}).then(function(data){
			$scope.item = data;
			if ($scope.item.operators === undefined)
				$scope.item.operators = [];
		});
	} else {
		$scope.item = {
			config_version_id: $scope.version.id,
			operators: []
		};
	}

	$scope.addOperator = function() {
		$scope.item.operators.push({operator_id: null, priority: 1, weight: 100});
	}

	$scope.removeOperator = function(index) {
		$scope.item.operators.splice(index, 1);
	}


	$scope.save = function()
	{
		RouteCase.save($scope.item).then(function(response) {
			$modalInstance.close();
		});
	}

	$scope.back = function()
	{
		$modalInstance.dismiss();
	}
};