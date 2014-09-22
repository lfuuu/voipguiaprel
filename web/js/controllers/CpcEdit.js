var CpcEditCtrl = function($scope, Cpc, params, $modalInstance, $window) {

	if (params.id) {
		Cpc.get({id: params.id}).then(function(data){
			$scope.item = data;
		});
	} else {
		$scope.item = {
			config_version_id: $scope.version.id
		};
	}

	$scope.save = function()
	{
		Cpc.save($scope.item).then(function(response) {
			$modalInstance.close();
		});
	}

	$scope.back = function()
	{
		$modalInstance.dismiss();
	}
};