var SettingsEditCtrl = function ($scope, $window, Settings, List, $modalInstance, params) {
  $scope.title = 'Общие настройки';
  $scope.name_changed = false;

  if (params && params.server_id) {
    $scope.server_id = params.server_id;
  } else {
    $scope.server_id = $scope.server.id;
  }

  Settings.get({server_id: $scope.server_id}).then(function (data) {
    $scope.item = data;
    $scope.item.trunk_groups = (data.trunk_groups == null) ? [] : data.trunk_groups.replace('{', '').replace('}', '').split(',');
    $scope.item.fsb_numa_blacklist_ids = (data.fsb_numa_blacklist_ids == null) ? [] : data.fsb_numa_blacklist_ids.replace('{', '').replace('}', '').split(',');
    $scope.item.fsb_numb_blacklist_ids = (data.fsb_numb_blacklist_ids == null) ? [] : data.fsb_numb_blacklist_ids.replace('{', '').replace('}', '').split(',');

    if ($scope.item.ast_trunk_group_id || $scope.item.ast_outcome_id) {
      $scope.vpbx_type_id = 2;
    } else {
      $scope.vpbx_type_id = 1;
    }
  });

  List.prefixlist().then(function (data) {
    $scope.prefixlist_list = data;
  });
  
  List.mvnoPartner().then(function (data) {
    $scope.mvno_partner_list = data;
  });

  $scope.save = function () {
    switch ($scope.vpbx_type_id) {
      case 1:
        $scope.item.ast_trunk_group_id = null;
        $scope.item.ast_outcome_id = null;
        break;
      case 2:
        $scope.item.vats_trunk_id = null;
        break;
      default:
        break;
    }

    Settings.save($scope.item).then(function (response) {
      if ($scope.name_changed) {
        $window.location.reload();
      } else {
        $modalInstance.close();
      }
    });
  };

  List.trunkGroup().then(function (data) {
    $scope.trunk_group_list = data;
  });

  List.number(2, $scope.server_id).then(function (data) {
    $scope.numbers = data;
  });

  $scope.setVpbxType = function (type_id) {
    $scope.vpbx_type_id = type_id;
  };

  $scope.back = function () {
    $modalInstance.dismiss();
  };

  $scope.nameChanged = function () {
    $scope.name_changed = true;
  };

  $scope.addPrefixlist = function () {
    $scope.item.hub_number_capacity.push({id: ''});
  };

  $scope.removePrefixlist = function (index) {
    $scope.item.hub_number_capacity.splice(index, 1);
  };
  
  $scope.addMvnoLink = function () {
    $scope.item.mvno_link.push({number_capacity: [], mvno_trunk_ids: [], trunk_groups: [],
        ported_number_prefixes: [], excluded_number_prefixes: [], routing_number: ''});
  };

  $scope.removeMvnoLink = function (index) {
    $scope.item.mvno_link.splice(index, 1);
  };
};
