var TrunkEditCtrl = function($scope, Trunk, params, $modalInstance) {

	if (params.id) {
		Trunk.get({id: params.id}).then(function(data){
			$scope.item = data;
		});
	} else {
		$scope.item = {
			config_version_id: $scope.version.id
		};
	}

	$scope.save = function() {
		Trunk.save($scope.item).then(function(response) {
			$modalInstance.close();
		});
	}

	$scope.back = function() {
		$modalInstance.dismiss();
	}
};