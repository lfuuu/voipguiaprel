app.controller('MainMarketplaceCtrl', function ($rootScope, $scope, $window, List, Trunk, ServiceTrunkRouting, $cookies, $timeout, $modal, Redirect) {
  $rootScope.userName = userName;
  $rootScope.userId = userId;

  $scope.isEditable = false;

  $scope.closeErrorsPopup = function () {
    $rootScope.popupErrors = false;
  };

  $scope.drawTable = function (data) {
    var findRegionInList = function (element) {
      if (element.name == this.name) {
        return true;
      }
    };

    var encapsulateAsyncRegion = function (data, hubKey, regionKey, regionItem) {
      List.trunkGroupForMarketplace(data[hubKey].items[regionKey].id).then(function (callResult) {
        var index = $scope.list.findIndex(findRegionInList, regionItem);

        var count = 0;
        for (var pTrunkKey in data[hubKey].items[regionKey].items) {
          var pTrunkFlag = true;

          for (var lTrunkKey in data[hubKey].items[regionKey].items[pTrunkKey].items) {
            var item = data[hubKey].items[regionKey].items[pTrunkKey].items[lTrunkKey];

            var trunkItem = {
              l_trunk_id: item.l_trunk_id,
              p_trunk_id: item.p_trunk_id,
              is_l_trunk: true,
              p_trunk_name: (pTrunkFlag ? item.p_trunk_name_basic : " "),
              l_trunk_name: item.l_trunk_name_basic,
              client_account_id: item.client_account_id,
              hub_id: item.hub_id,
              region_id: item.server_id,
              organization_name: item.organization_name,
              price_name_basic: item.price_name_basic,
              uplink_enabled: item.uplink_enabled,
              trunk_groups: (item.trunk_groups == null) ? '' : item.trunk_groups.replace('{', '').replace('}', '').split(','),
              trunk_group_list: callResult
            };

            if (index !== -1) {
              $scope.list.splice(index + 1 + count, 0, trunkItem);
            } else {
              $scope.list.push(trunkItem);
            }

            pTrunkFlag = false;
            count++;
          }
        }
      });
    };

    for (var hubKey in data) {

      var hubItem = {
        is_hub: true,
        hub_id: data[hubKey].id,
        name: hubKey
      };

      var headerItem = {
        is_header: true
      };

      $scope.list.push(hubItem);
      $scope.list.push(headerItem);

      for (var regionKey in data[hubKey].items) {
        var regionItem = {
          is_region: true,
          hub_id: data[hubKey].id,
          region_id: data[hubKey].items[regionKey].id,
          name: regionKey
        };

        $scope.list.push(regionItem);

        encapsulateAsyncRegion(data, hubKey, regionKey, regionItem);
      }
    }
  };

  $scope.drawViewTable = function (data) {
    for (var hubKey in data) {
      var hubItem = {
        is_hub: true,
        hub_id: data[hubKey].id,
        name: hubKey
      };

      var headerItem = {
        is_header: true
      };

      $scope.list.push(hubItem);
      $scope.list.push(headerItem);

      for (var regionKey in data[hubKey].items) {
        var regionItem = {
          is_region: true,
          hub_id: data[hubKey].id,
          region_id: data[hubKey].items[regionKey].id,
          name: regionKey
        };

        $scope.list.push(regionItem);

        for (var pTrunkKey in data[hubKey].items[regionKey].items) {
          var pTrunkFlag = true;

          for (var lTrunkKey in data[hubKey].items[regionKey].items[pTrunkKey].items) {
            var item = data[hubKey].items[regionKey].items[pTrunkKey].items[lTrunkKey];

            var trunkItem = {
              l_trunk_id: item.l_trunk_id,
              p_trunk_id: item.p_trunk_id,
              is_l_trunk: true,
              p_trunk_name: (pTrunkFlag ? item.p_trunk_name_basic : " "),
              l_trunk_name: item.l_trunk_name_basic,
              client_account_id: item.client_account_id,
              hub_id: item.hub_id,
              region_id: item.server_id,
              organization_name: item.organization_name,
              price_name_basic: item.price_name_basic,
              uplink_enabled: item.uplink_enabled,
              trunk_group_name: item.trunk_group_name,
            };

            $scope.list.push(trunkItem);

            pTrunkFlag = false;
          }
        }
      }
    }
  };

  $scope.initTable = function () {
    Trunk.readMarketplace().then(function (data) {
      $scope.list = [];
      $scope.isEditable = true;
      $scope.drawTable(data);
    });
  };

  $scope.initViewTable = function () {
    Trunk.readMarketplace().then(function (data) {
      $scope.list = [];
      $scope.drawViewTable(data);
    });
  };

  $scope.init = function () {
    if (userPermissions['marketplace_edit']) {
      $scope.initTable();
    } else if (userPermissions['marketplace_list']) {
      $scope.initViewTable();
    } else {
      return null;
    }
  };

  $scope.clickTrunk = function (id, regionId) {
    if (!userPermissions['trunk_edit'] || !id) {
      return;
    }

    Redirect.trunkEditByServer(id, regionId).then(function () {
      $scope.init();
    });
  };

  $scope.clickRegion = function (serverId) {
    if (!userPermissions['general_settings_edit']) {
      return;
    }

    Redirect.settingsById(serverId).then(function () {
      $scope.init();
    });
  };

  $scope.clickNumber = function (id) {
    if (!userPermissions['number_edit'] || !id) {
      return;
    }

    Redirect.numberEdit(id).then(function () {
      $scope.init();
    });
  };

  $scope.toggleUplinkEnabled = function (item) {
    item.uplink_enabled = !item.uplink_enabled;

    ServiceTrunkRouting.save({id: item.l_trunk_id, uplink_enabled: item.uplink_enabled}).then(function () {
      // $scope.init();
    });
  };

  $scope.changeTrunkGroups = function (id, trunk_groups) {
    ServiceTrunkRouting.save({id: id, trunk_groups: trunk_groups}).then(function () {
      // $scope.init();
    });
  };

  $scope.toggleHubVisibility = function (item) {
    var findHubInList = function (element) {
      if (element.name == this.name) {
        return true;
      }
    };

    var index = $scope.list.findIndex(findHubInList, item);

    for (var i = index; i < $scope.list.length; i++) {
      if ($scope.list[i]['is_hub'] == true && $scope.list[i]['hub_id'] != item.hub_id) {
        break;
      }
      $scope.list[i]['hidden'] = !$scope.list[i]['hidden'];
    }
  };

  $scope.trunkInfo = function (id) {
    $window.open('/trunk/full-info?trunkId=' + id);
  };

  $scope.init();
});