var OperatorListCtrl = function($scope, Operator, Redirect, $window) {

	$scope.init = function(tab) {
		if (tab) tab.title = 'Операторы';

		Operator.read({config_version_id: $scope.version.id}).then(function(data){
			$scope.list = data;
		});
	};

	$scope.clickCreate = function() {
		Redirect.operatorCreate().then(function () {
			$scope.init();
		});
	};

	$scope.clickItem = function(item) {
		if (window.getSelection().type == 'Range') return;

		Redirect.operatorEdit(item.id).then(function () {
			$scope.init();
		});
	};

	$scope.clickCopyFromBilling = function() {
		Operator.copyFromBilling({config_version_id: $scope.version.id}).then(function(data){
			alert(data.message);
			$scope.init();
		})
	};

	$scope.deleteItem = function(item)
	{
		if (!$window.confirm('Удалить?')) return;

		Operator.delete(item.id).then(function(response) {
			$scope.init()
		});
	};
};