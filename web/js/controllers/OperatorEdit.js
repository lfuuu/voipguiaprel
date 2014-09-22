var OperatorEditCtrl = function($scope, Operator, params, $modalInstance, $window) {

	if (params.id) {
		Operator.get({id: params.id}).then(function(data){
			$scope.item = data;
		});
	} else {
		$scope.item = {
			config_version_id: $scope.version.id,
			default_priority: 0,
			source_rule_default_allowed: false,
			destination_rule_default_allowed: false,
			priorities: [],
			rules: []
		};
	}

	$scope.addPriority = function() {
		$scope.item.priorities.push({prefixlist_id: '', priority: 0});
	}

	$scope.removePriority = function(index) {
		$scope.item.priorities.splice(index, 1);
	}

	$scope.addRule = function(outgoing) {
		$scope.item.rules.push({prefixlist_id: '', trunk_group_id: '', outgoing: outgoing});
	}

	$scope.removeRule = function(index) {
		$scope.item.rules.splice(index, 1);
	}

	$scope.save = function()
	{
		Operator.save($scope.item).then(function(response) {
			$modalInstance.close();
		});
	}

	$scope.back = function()
	{
		$modalInstance.dismiss();
	}
};