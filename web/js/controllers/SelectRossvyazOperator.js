var SelectRossvyazOperatorCtrl = function($scope, Billing, params, $modalInstance, $window) {

	Billing.operators().then(function(data){
		$scope.list = data;
	});

	$scope.clickItem = function(item)
	{
		$modalInstance.close(item);
	}

	$scope.back = function()
	{
		$modalInstance.dismiss();
	}
};