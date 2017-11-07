var SettingsEditCtrl = function($scope, Settings, $modalInstance) {
	$scope.title = 'Общие настройки';

	Settings.get({server_id: $scope.server.id}).then(function(data){
		$scope.item = data;

		if ($scope.item.vats_trunk_id) {
			$scope.vpbx_type_id = 1;
		} else if ($scope.item.ast_trunk_group_id) {
			$scope.vpbx_type_id = 2;
		}
	});


	$scope.save = function()
	{
        switch ($scope.vpbx_type_id) {
            case 1:
                $scope.item.ast_trunk_group_id = null;
                break;
            case 2:
                $scope.item.vats_trunk_id = null;
                break;
            case 3:
                $scope.item.vats_trunk_id = null;
                $scope.item.ast_trunk_group_id = null;
                break;
            default:
                break;
        }

		Settings.save($scope.item).then(function(response) {
			$modalInstance.close();
		});
	};

	$scope.setVpbxType = function (type_id) {
		$scope.vpbx_type_id = type_id;
	}

	$scope.back = function()
	{
		$modalInstance.dismiss();
	};

};
