var OutcomeListCtrl = function($scope, Outcome, Redirect, $window) {

  $scope.sortType = 'name';
  $scope.sortReverse = false;
  $scope.searchQuery = '';

  $scope.filterFields = [
    'name', 'calling_station_id', 'called_station_id', 'server_id'
  ];

	$scope.init = function(tab) {
		if (tab) tab.title = 'Outcomes';

		Outcome.read({server_id: $scope.server.id}).then(function(data){
			$scope.list = data;
		});
	};

	$scope.clickCreate = function() {
		Redirect.outcomeCreate().then(function () {
			$scope.init();
		});
	};

	$scope.clickItem = function(item) {
        if (!userPermissions['outcome_list']) {
            return;
        }

		if (window.getSelection().type == 'Range') return;

		Redirect.outcomeEdit(item.id).then(function () {
			$scope.init();
		});
	};

	$scope.deleteItem = function(item)
	{
		if (!$window.confirm('Удалить?')) return;

		Outcome.delete(item.id).then(function(response) {
			$scope.init()
		});
	};
};