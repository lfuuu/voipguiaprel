var OutcomeEditCtrl = function($scope, Outcome, params, $modalInstance, $window) {

	if (params.id) {
		Outcome.get({id: params.id}).then(function(data){
			$scope.item = data;
			$scope.setType($scope.item.type_id);
		});
	} else {
		$scope.item = {
            server_id: $scope.server.id
		};
	}

	$scope.setType = function(type_id) {
		$scope.item.type_id = type_id;
	}


	$scope.save = function()
	{
		if ($scope.item.type_id == 1) {
			$scope.item.route_case_id = null;
			$scope.item.release_reason_id = null;
			$scope.item.airp_id = null;
			$scope.item.calling_station_id = null;
			$scope.item.called_station_id= null;
		}
		if ($scope.item.type_id == 2) {
			$scope.item.release_reason_id = null;
			$scope.item.airp_id = null;
		}
		if ($scope.item.type_id == 3) {
			$scope.item.route_case_id = null;
			$scope.item.airp_id = null;
			$scope.item.calling_station_id = null;
			$scope.item.called_station_id= null;
		}
		if ($scope.item.type_id == 4) {
			$scope.item.route_case_id = null;
			$scope.item.release_reason_id = null;
		}
		if ($scope.item.type_id == 5) {
			$scope.item.route_case_id = null;
			$scope.item.release_reason_id = null;
			$scope.item.airp_id = null;
			$scope.item.calling_station_id = null;
			$scope.item.called_station_id= null;
		}

		Outcome.save($scope.item).then(function(response) {
			$modalInstance.close();
		});
	}

	$scope.back = function()
	{
		$modalInstance.dismiss();
	}
};