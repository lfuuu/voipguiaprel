var RouteTableListCtrl = function($scope, RouteTable, Redirect, $window) {

  $scope.sortType = 'name';
  $scope.sortReverse = false;
  $scope.searchQuery = '';

	$scope.init = function(tab) {
		if (tab) tab.title = 'Таблицы маршрутизации';

		RouteTable.read({server_id: $scope.server.id}).then(function(data){
			$scope.list = data;
		});
	};

	$scope.clickCreate = function() {
		Redirect.routeTableCreate().then(function () {
			$scope.init();
		});
	};

	$scope.clickItem = function(item) {
        if (!userPermissions['route_table_edit']) {
            return;
        }

		if (window.getSelection().type == 'Range') return;

		Redirect.routeTableEdit(item.id).then(function () {
			$scope.init();
		});
	};

	$scope.deleteItem = function(item)
	{
		if (!$window.confirm('Удалить?')) return;

		RouteTable.delete(item.id).then(function(response) {
			$scope.init()
		});
	};
};