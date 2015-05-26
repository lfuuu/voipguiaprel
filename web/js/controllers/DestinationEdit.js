var DestinationEditCtrl = function($scope, Destination, Prefixlist, params, $modalInstance, $window) {

	if (params.id) {
        Destination.get({id: params.id}).then(function(data){
			$scope.item = data;
			var prefixlist_ids = [];
			for(var i in $scope.item.prefixlist_ids) {
				prefixlist_ids.push({id: $scope.item.prefixlist_ids[i]})
			}
			$scope.item.prefixlist_ids = prefixlist_ids;
		});
	} else {
		$scope.item = {
            server_id: $scope.server.id,
			prefixlist_ids: []
		};
	}

	Prefixlist.list().then(function(data){
		$scope.prefixlistList = data;
	});

	$scope.addPrefixlist = function() {
		$scope.item.prefixlist_ids.push({id: null});
	};

	$scope.removePrefixlist = function(index) {
		$scope.item.prefixlist_ids.splice(index, 1);
	};


	$scope.save = function()
	{
		var data = angular.copy($scope.item);
		data.prefixlist_ids = [];
		for(var i in $scope.item.prefixlist_ids) {
			data.prefixlist_ids.push($scope.item.prefixlist_ids[i].id)
		}

        Destination.save(data).then(function(response) {
			$modalInstance.close();
		});
	}

	$scope.back = function()
	{
		$modalInstance.dismiss();
	}
};