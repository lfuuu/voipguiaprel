var UplinkListCtrl = function($scope, Uplink, Redirect, $window) {

  $scope.sortType = 'name';
  $scope.sortReverse = false;
  $scope.searchQuery = '';

	$scope.init = function(tab) {
		if (tab) tab.title = 'Аплинки';

		Uplink.read().then(function(data){
			$scope.list = data;
		});
	};

  $scope.collapseAll = function () {
    $scope.$broadcast('angular-ui-tree:collapse-all');
  };

  $scope.expandAll = function () {
    $scope.$broadcast('angular-ui-tree:expand-all');
  };

  $scope.$on('angular-ui-tree:collapse-all', function () {
    $scope.collapsed = true;
  });

  $scope.$on('angular-ui-tree:expand-all', function () {
    $scope.collapsed = false;
  });

  $scope.delete = function(id, level) {
    if (!$window.confirm('Удалить?')) return;

  	Uplink.delete(id, level).then(function(data){
      $scope.init();
		});
	};

	$scope.clickCreate = function() {
		Redirect.uplinkCreate({}).then(function () {
			$scope.init();
		});
	};

  $scope.add = function(hubId, regionId, pTrunkId) {
    Redirect.uplinkCreate({hub_id: hubId, region_id: regionId, p_trunk_id: pTrunkId}).then(function () {
      $scope.init();
    });
  };

	$scope.clickItem = function(id) {
    if (!userPermissions['uplink_edit']) {
        return;
    }

		if (window.getSelection().type == 'Range') return;

		Redirect.uplinkEdit(id).then(function () {
			$scope.init();
		});
	};
};