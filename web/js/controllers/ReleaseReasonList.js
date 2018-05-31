var ReleaseReasonListCtrl = function($scope, ReleaseReason, Redirect, $window) {

  $scope.sortType = 'name';
  $scope.sortReverse = false;
  $scope.searchQuery = '';

  $scope.filterFields = [
    'name', 'server_id'
  ];

  $scope.init = function(tab) {
		if (tab) tab.title = 'Release reason';

		ReleaseReason.read({server_id: $scope.server.id}).then(function(data){
			$scope.list = data;
		});
	};

	$scope.clickCreate = function() {
		Redirect.releaseReasonCreate().then(function () {
			$scope.init();
		});
	};

	$scope.clickItem = function(item) {
        if (!userPermissions['release_reason_edit']) {
            return;
        }

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