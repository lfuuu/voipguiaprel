var NumberEditCtrl = function($scope, Number, Redirect, Prefixlist, params, $modalInstance, $window) {

	if (params.id) {
		Number.get({id: params.id}).then(function(data){
			$scope.item = data;
			var prefixlist_ids = [];
			for(var i in $scope.item.prefixlist_ids) {
				prefixlist_ids.push({id: $scope.item.prefixlist_ids[i]})
			}
			$scope.item.prefixlist_ids = prefixlist_ids;
		});

		Number.findUsagesInRouteTables({id: params.id}).then(function(data){
		    $scope.usagesInRouteTables = data;
		});

		Number.findUsagesInTrunkPriority({id: params.id}).then(function(data){
		    $scope.usagesInTrunkPriority = data;
		});

		Number.findUsagesInTrunkRules({id: params.id}).then(function(data){
		    $scope.usagesInTrunkRules = data;
		});

        Number.findUsagesInStatRules({id: params.id}).then(function(data){
            $scope.usagesInStatRules = data;
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

		Number.save(data).then(function(response) {
			$modalInstance.close();
		});
	};

	$scope.back = function()
	{
		$modalInstance.dismiss();
	};

  $scope.clickRouteTableItem = function(item) {
    if (window.getSelection().type == 'Range') return;

    Redirect.routeTableEdit(item.id).then(function () {
      $scope.init();
    });
  };

  $scope.clickTrunkItem = function(item) {
    if (window.getSelection().type == 'Range') return;

    Redirect.trunkEdit(item.trunk_id).then(function () {
      $scope.init();
    });
  };
};