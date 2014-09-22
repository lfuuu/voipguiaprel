var TrunkGroupEditCtrl = function($scope, TrunkGroup, params, $modalInstance) {

	if (params.id) {
		TrunkGroup.get({id: params.id}).then(function(data){
			$scope.item = data;
			var trunk_ids = [];
			for(var i in $scope.item.trunk_ids) {
				trunk_ids.push({id: $scope.item.trunk_ids[i]})
			}
			$scope.item.trunk_ids = trunk_ids;
		});
	} else {
		$scope.item = {
			config_version_id: $scope.version.id,
			trunk_ids: []
		};
	}

	$scope.addTrunk = function() {
		$scope.item.trunk_ids.push({id: null});
	};

	$scope.removeTrunk = function(index) {
		$scope.item.trunk_ids.splice(index, 1);
	};


	$scope.save = function()
	{
		var data = angular.copy($scope.item);
		data.trunk_ids = [];
		for(var i in $scope.item.trunk_ids) {
			data.trunk_ids.push($scope.item.trunk_ids[i].id)
		}

		TrunkGroup.save(data).then(function(response) {
			$modalInstance.close();
		});
	}

	$scope.back = function()
	{
		$modalInstance.dismiss();
	}
};