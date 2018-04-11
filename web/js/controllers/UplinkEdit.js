var UplinkEditCtrl = function ($scope, Uplink, Redirect, Server, Trunk, List, params, $modalInstance, $window) {
  var watchers = {
    hub_id: function (newValue, oldValue) {
      if (newValue != oldValue) {
        $scope.item.region_id = null;
        $scope.item.p_trunk_id = null;
        $scope.item.l_trunk_id = null;

        $scope.getRegionList();
      }
    },
    region_id: function (newValue, oldValue) {
      if (newValue != oldValue) {
        $scope.item.p_trunk_id = null;
        $scope.item.l_trunk_id = null;

        if (newValue) {
          $scope.getPTrunkList();
        }
      }
    },
    p_trunk_id: function (newValue, oldValue) {
      if (newValue != oldValue) {
        $scope.item.l_trunk_id = null;

        $scope.getLTrunkList();
      }
    }
  };

  $scope.setItem = function (item) {
    $scope.item = item;

    $scope.$watch('item.hub_id', watchers.hub_id);
    $scope.$watch('item.region_id', watchers.region_id);
    $scope.$watch('item.p_trunk_id', watchers.p_trunk_id);
  };

  if (params && params.id) {
    Uplink.get({id: params.id}).then(function (originalData) {
      Server.listByHubWithContract({hub_id: originalData.hub_id}).then(function(data) {
        $scope.regionList = data;

        Trunk.listByServerWithContract(originalData.region_id).then(function(data) {
          $scope.pTrunkList = data;

          Trunk.serviceTrunks(originalData.p_trunk_id).then(function(data) {
            $scope.lTrunkList = data;

            if (originalData.hub_id == 0) {
              originalData.hub_id = 'none';
            }

            $scope.setItem(originalData);
          });
        });
      });
    });
  } else {
    var item = {
      hub_id: 'none',
      active: true,
      active_mode: 1
    };

    if (params && params.hub_id) {
      if (params.hub_id == 0) {
        item.hub_id = 'none';
      } else {
        item.hub_id = params.hub_id;
      }

      Server.listByHubWithContract({hub_id: params.hub_id}).then(function(data) {
        $scope.regionList = data;

        if (params.region_id) {
        item.region_id = params.region_id;
          Trunk.listByServerWithContract(params.region_id).then(function (data) {
            $scope.pTrunkList = data;

            if (params.p_trunk_id) {
            item.p_trunk_id = params.p_trunk_id;
              Trunk.serviceTrunks(params.p_trunk_id).then(function (data) {
                $scope.lTrunkList = data;

                $scope.setItem(item);
              });
            } else {
              $scope.setItem(item);
            }
          });
        } else {
          $scope.setItem(item);
        }
      });
    } else {
      $scope.setItem(item);
    }
  }

  $scope.activeTypes = List.uplinkActiveType();

  $scope.save = function () {
    Uplink.save($scope.item).then(function (response) {
      $modalInstance.close();
    });
  };

  $scope.getRegionList = function() {
    if ($scope.item.hub_id == 'new') {
      return;
    }

    var hub = '';

    if ($scope.item.hub_id != 'none') {
      hub = $scope.item.hub_id;
    }

    Server.listByHubWithContract({hub_id: hub}).then(function(data) {
      $scope.regionList = data;
    });
  };

  $scope.getPTrunkList = function() {
    Trunk.listByServerWithContract($scope.item.region_id).then(function(data) {
      $scope.pTrunkList = data;
    });
  };

  $scope.getLTrunkList = function() {
    Trunk.serviceTrunks($scope.item.p_trunk_id).then(function(data) {
      $scope.lTrunkList = data;
    });
  };

  $scope.back = function () {
    $modalInstance.dismiss();
  };
};