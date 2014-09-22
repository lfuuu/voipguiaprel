var ReleaseReasonListCtrl = function($scope, ReleaseReason, Redirect, $window) {

	$scope.init = function(tab) {
		if (tab) tab.title = 'Release reason';

		ReleaseReason.read({config_version_id: $scope.version.id}).then(function(data){
			$scope.list = data;
		});
	};

	$scope.clickCreate = function() {
		Redirect.releaseReasonCreate().then(function () {
			$scope.init();
		});
	};

	$scope.clickItem = function(item) {
		if (window.getSelection().type == 'Range') return;

		Redirect.releaseReasonEdit(item.id).then(function () {
			$scope.init();
		});
	};

	$scope.deleteItem = function(item)
	{
		if (!$window.confirm('Удалить?')) return;

		ReleaseReason.delete(item.id).then(function(response) {
			$scope.init()
		});
	};
};