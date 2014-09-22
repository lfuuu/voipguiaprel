var SettingsEditCtrl = function($scope, Settings, $modalInstance) {
	$scope.title = 'Общие настройки';

	Settings.get({config_version_id: $scope.version.id}).then(function(data){
		$scope.item = data;
	});


	$scope.save = function()
	{
		Settings.save($scope.item).then(function(response) {
			$scope.version.name = $scope.item.name;
			$modalInstance.close();
		});
	}

	$scope.back = function()
	{
		$modalInstance.dismiss();
	}

	$scope.clone = function()
	{
		Settings.clone($scope.item).then(function(response) {
			window.location.href = '/c' + response.config_version_id
		});
	}
};