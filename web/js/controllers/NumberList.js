var NumberListCtrl = function($scope, Number, Redirect, $window) {

  $scope.sortType = 'name';
  $scope.sortReverse = false;
  $scope.searchQuery = '';

  $scope.filterFields = [
    'name', 'server_id'
  ];

	$scope.init = function(tab) {
		if (tab) tab.title = 'A/B/C/GT номера';

		Number.read({server_id: $scope.server.id}).then(function(data){
			$scope.list = data;
		});
	};

	$scope.clickCreate = function() {
		Redirect.numberCreate().then(function () {
			$scope.init();
		});
	};

	$scope.clickItem = function(item) {
        if (!userPermissions['number_edit']) {
            return;
        }

		if (window.getSelection().type == 'Range') return;

		Redirect.numberEdit(item.id).then(function () {
			$scope.init();
		});
	};

	$scope.deleteItem = function(item)
	{
		if (!$window.confirm('Удалить?')) return;

		Number.delete(item.id).then(function(response) {
			$scope.init()
		});
	};
};