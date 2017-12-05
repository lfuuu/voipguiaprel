var SettingsEditCtrl = function($scope, Settings, List, $modalInstance) {
	$scope.title = 'Общие настройки';

	Settings.get({server_id: $scope.server.id}).then(function(data){
		$scope.item = data;

        if ($scope.item.ast_trunk_group_id || $scope.item.ast_outcome_id) {
			$scope.vpbx_type_id = 2;
		} else {
			$scope.vpbx_type_id = 1;
		}
	});

	$scope.save = function()
	{
        switch ($scope.vpbx_type_id) {
            case 1:
                $scope.item.ast_trunk_group_id = null;
                $scope.item.ast_outcome_id = null;
                break;
            case 2:
                $scope.item.vats_trunk_id = null;
                break;
            default:
                break;
        }

		Settings.save($scope.item).then(function(response) {
			$modalInstance.close();
		});
	};

	List.number(2).then(function(data){
		$scope.numbers = data;
	});

	$scope.setVpbxType = function (type_id) {
		$scope.vpbx_type_id = type_id;
	}

	$scope.back = function()
	{
		$modalInstance.dismiss();
	};

};
