var SettingsEditCtrl = function($scope, Settings, $modalInstance) {
	$scope.title = 'Общие настройки';

	Settings.get({server_id: $scope.server.id}).then(function(data){
		$scope.item = data;
	});


	$scope.save = function()
	{
		Settings.save($scope.item).then(function(response) {
			$modalInstance.close();
		});
	};

	$scope.back = function()
	{
		$modalInstance.dismiss();
	};

};
