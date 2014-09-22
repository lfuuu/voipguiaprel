var RoutingReportCtrl = function($scope, Billing, $http) {

	$scope.filter = {
		configVersionId: $scope.version.id,
		offset: 0,
		limit: 20
	};

	$scope.init = function(tab) {
		if (tab) tab.title = 'Отчет по маршрутизации';
	};

	Billing.countries().then(function(data){
		$scope.countries = data;
	});

	Billing.regions().then(function(data){
		$scope.regions = data;
	});

	$scope.make = function()
	{
		$scope.filter.offset = 0;
		$scope.processReport();
	};

	$scope.recalc = function()
	{
		$scope.filter.offset = 0;
		$scope.processReport('recalc');
	};

	$scope.firstPage = function()
	{
		$scope.filter.offset = 0;
		$scope.processReport();
	};

	$scope.nextPage = function()
	{
		$scope.filter.offset += $scope.filter.limit;
		$scope.processReport();
	};

	$scope.previousPage = function()
	{
		$scope.filter.offset -= $scope.filter.limit;
		if ($scope.filter.offset < 0) $scope.filter.offset = 0;
		$scope.processReport();
	};

	$scope.export = function()
	{
	};

	$scope.processReport = function (action) {
		if (action == undefined) action = 'make';
		$scope.processing = true;
		$http.post('/routing-report/' + action, $scope.filter)
			.success(function(data){
				document.getElementById('routingReportPlace').innerHTML = data;
				$scope.hasData = true;
				$scope.processing = false;
			})
			.error(function(){
				document.getElementById('routingReportPlace').innerHTML = '';
				$scope.processing = false;
				$scope.hasData = false;
			})
		;
	}
};
