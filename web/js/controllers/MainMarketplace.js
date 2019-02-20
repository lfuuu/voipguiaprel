app.controller('MainMarketplaceCtrl', function ($rootScope, $scope, $window, List, Trunk, ServiceTrunkRouting, $cookies, $timeout, $modal, Redirect) {
  $rootScope.userName = userName;
  $rootScope.userId = userId;

  $scope.closeErrorsPopup = function () {
    $rootScope.popupErrors = false;
  };

  $scope.drawTable = function (data) {
    var findHubInList = function (element, index, array) {
      if (element.name == this.name) {
        return true;
      }
    };

    var encapsulateAsyncRegion = function (data, hubKey, regionKey, hubItem, headerItem) {
      List.trunkGroup(data[hubKey].items[regionKey].id).then(function (callResult) {
        var index = $scope.list.findIndex(findHubInList, hubItem);
        if (index === -1) {
          $scope.list.push(hubItem);
          $scope.list.push(headerItem);
        }

        var regionItem = {
          is_region: true,
          hub_id: data[hubKey].id,
          region_id: data[hubKey].items[regionKey].id,
          name: regionKey
        };

        if (index !== -1) {
          $scope.list.splice(index + 2, 0, regionItem);
        } else {
          $scope.list.push(regionItem);
        }

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
              $scope.list.splice(index + 3 + count, 0, trunkItem);
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

      for (var regionKey in data[hubKey].items) {
        encapsulateAsyncRegion(data, hubKey, regionKey, hubItem, headerItem);
      }
    }
  };

  $scope.initTable = function () {
    Trunk.readMarketplace().then(function (data) {
      $scope.list = [];
      $scope.drawTable(data);
    });
  };

  $scope.init = function () {
    $scope.initTable();
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

  $scope.trunkInfo = function (id) {
    $window.open('/trunk/full-info?trunkId=' + id);
  };

  $scope.init();
});