var CpcListCtrl = function($scope, Cpc, Redirect, $window) {

  $scope.sortType = 'name';
  $scope.sortReverse = false;
  $scope.searchQuery = '';

	$scope.init = function(tab) {
		if (tab) tab.title = 'CPC';

		Cpc.read().then(function(data){
			$scope.list = data;
		});
	};

	$scope.clickCreate = function() {
		Redirect.cpcCreate().then(function () {
			$scope.init();
		});
	};

	$scope.clickItem = function(item) {
        if (!userPermissions['cpc_edit']) {
            return;
        }

		if (window.getSelection().type == 'Range') return;

		Redirect.cpcEdit(item.id).then(function () {
			$scope.init();
		});
	};

	$scope.deleteItem = function(item)
	{
		if (!$window.confirm('Удалить?')) return;

		Cpc.delete(item.id).then(function(response) {
			$scope.init()
		});
	};
};