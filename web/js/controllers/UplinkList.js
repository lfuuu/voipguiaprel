var UplinkListCtrl = function ($scope, List, Uplink, Redirect, $window) {

  $scope.sortType = 'name';
  $scope.sortReverse = false;
  $scope.searchQuery = '';
  $scope.list = [];

  $scope.treeLoaded = false;
  $scope.activeModeList = List.uplinkActiveMode();

  $scope.drawTable = function (data) {
    for (var hubKey in data) {
      $scope.list.push({
        is_hub: true,
        hub_id: data[hubKey].id,
        name: hubKey
      });

      $scope.list.push({
        is_header: true
      });

      for (var regionKey in data[hubKey].items) {
        $scope.list.push({
          is_region: true,
          hub_id: data[hubKey].id,
          region_id: data[hubKey].items[regionKey].id,
          name: regionKey
        });


        for (var pTrunkKey in data[hubKey].items[regionKey].items) {
          var pTrunkFlag = true;

          for (var lTrunkKey in data[hubKey].items[regionKey].items[pTrunkKey].items) {
            var item = data[hubKey].items[regionKey].items[pTrunkKey].items[lTrunkKey];

            $scope.list.push({
              l_trunk_id: item.l_trunk_id,
              p_trunk_id: item.p_trunk_id,
              is_l_trunk: true,
              p_trunk_name: (pTrunkFlag ? item.p_trunk_name_basic : " "),
              l_trunk_name: item.l_trunk_name_basic,
              a_name: item.a_name,
              b_name: item.b_name,
              a_id: item.a_id,
              b_id: item.b_id,
              price_name: item.price_name,
              is_active: item.active,
              is_active_text: (item.active ? 'Вкл' : 'Выкл'),
              active_mode: $scope.activeModeList[(item.active_mode - 1)].name,
              client_account_id: item.client_account_id,
              hub_id: item.hub_id,
              region_id: item.region_id,
              has_road: item.has_road
            });

            pTrunkFlag = false;
          }
        }
      }
    }
  };

  $scope.initTable = function () {
    Uplink.read().then(function (data) {
      $scope.list = [];
      $scope.drawTable(data);
    });
  };

  $scope.initTree = function (tab) {
    Uplink.readTree().then(function (data) {
      $scope.tree = data;
    });
  };

  $scope.init = function (tab) {
    if (tab) tab.title = 'Аплинки';

    $scope.initTable();
  };

  $scope.activateTab = function (pageId) {
    var tabCtrl = document.getElementById('tabCtrl');
    var pageToActivate = document.getElementById(pageId);
    for (var i = 0; i < tabCtrl.childNodes.length; i++) {
      var node = tabCtrl.childNodes[i];
      if (node.nodeType == 1) {
        node.style.display = (node == pageToActivate) ? 'block' : 'none';
      }
    }

    if (!$scope.treeLoaded) {
      $scope.treeLoaded = true;

      Uplink.readTree().then(function (data) {
        $scope.tree = data;
      });
    }
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

  $scope.delete = function (id, level) {
    if (!$window.confirm('Удалить?')) return;

    Uplink.delete(id, level).then(function (data) {
      $scope.initTable();

      if ($scope.treeLoaded) {
        $scope.initTree();
      }
    });
  };

  $scope.clickCreate = function () {
    Redirect.uplinkCreate({}).then(function () {
      $scope.initTable();

      if ($scope.treeLoaded) {
        $scope.initTree();
      }
    });
  };

  $scope.add = function (hubId, regionId, pTrunkId) {
    Redirect.uplinkCreate({hub_id: hubId, region_id: regionId, p_trunk_id: pTrunkId}).then(function () {
      $scope.initTable();

      if ($scope.treeLoaded) {
        $scope.initTree();
      }
    });
  };

  $scope.clickItem = function (id) {
    if (!userPermissions['uplink_edit']) {
      return;
    }

    Redirect.uplinkEdit(id).then(function () {
      $scope.initTable();

      if ($scope.treeLoaded) {
        $scope.initTree();
      }
    });
  };

  $scope.clickTrunk = function (id) {
    if (!userPermissions['trunk_edit'] || !id) {
      return;
    }

    Redirect.trunkEdit(id).then(function () {
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

  $scope.trunkInfo = function (id) {
    $window.open('/trunk/full-info?trunkId=' + id);
  };

};