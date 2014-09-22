var RoutingEditCtrl = function($scope, Routing, Number, Outcome, Redirect) {

	$scope.init = function(tab) {
		if (tab) tab.title = 'Таблица маршрутизации';

		Routing.get({config_version_id: $scope.version.id}).then(function(data){
			$scope.item = data;
		});
	};

	Number.list().then(function(data){
		$scope.numberList = data;
	});

	Outcome.list().then(function(data){
		$scope.outcomeList = data;
	});

	$scope.addRoute = function() {
		$scope.item.routes.push({ a_number_id: null, b_number_id: null, outcome_id: null });
	};

	$scope.removeRoute = function(index) {
		$scope.item.routes.splice(index, 1);
	};

	$scope.save = function()
	{
		Routing.save($scope.item).then(function(response) {
			$scope.init();
		});
	};

	$scope.openNumber = function(itemId) {
		Redirect.numberEdit(itemId);
	};

	$scope.openOutcome = function(itemId) {
		Redirect.outcomeEdit(itemId);
	};
};