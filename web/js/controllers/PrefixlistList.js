var PrefixlistListCtrl = function($scope, Prefixlist, Redirect, $window) {

	$scope.init = function(tab) {
		if (tab) tab.title = 'Списки префиксов';

		Prefixlist.read({server_id: $scope.server.id}).then(function(data){
			$scope.list = data;
		});
	};

	$scope.clickCreate = function() {
		Redirect.prefixlistCreate().then(function () {
			$scope.init();
		});
	}
	$scope.clickItem = function(item) {
		if (window.getSelection().type == 'Range') return;

		Redirect.prefixlistEdit(item.id).then(function () {
			$scope.init();
		});
	}

	$scope.deleteItem = function(item)
	{
		if (!$window.confirm('Удалить?')) return;

		Prefixlist.delete(item.id).then(function(response) {
			$scope.init()
		});
	}
};