var ReleaseReasonEditCtrl = function($scope, ReleaseReason, params, $modalInstance, $window) {

	if (params.id) {
		ReleaseReason.get({id: params.id}).then(function(data){
			$scope.item = data;
		});
	} else {
		$scope.item = {
			config_version_id: $scope.version.id
		};
	}

	$scope.save = function()
	{
		ReleaseReason.save($scope.item).then(function(response) {
			$modalInstance.close();
		});
	}

	$scope.back = function()
	{
		$modalInstance.dismiss();
	}
};