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

    if ($scope.item.ast_trunk_group_id || $scope.item.ast_outcome_id) {
      $scope.vpbx_type_id = 2;
    } else {
      $scope.vpbx_type_id = 1;
    }
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
};
