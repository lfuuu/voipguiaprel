var SettingsEditCtrl = function ($scope, $window, Settings, List, $modalInstance, params) {
  $scope.title = 'Общие настройки';
  $scope.name_changed = false;

  $scope.MTS_ANTIFRAUD = 1;
  $scope.EPVV_ANTIFRAUD = 2;
  $scope.EPVV_TRANSIT = 3;
  $scope.nnpMode = $scope.EPVV_ANTIFRAUD;

  if (params && params.server_id) {
    $scope.server_id = params.server_id;
  } else {
    $scope.server_id = $scope.server.id;
  }

  Settings.get({server_id: $scope.server_id}).then(function (data) {
    $scope.item = data || {};
    $scope.item.trunk_groups = (data.trunk_groups == null) ? [] : data.trunk_groups.replace('{', '').replace('}', '').split(',');
    $scope.item.fsb_numa_blacklist_ids = (data.fsb_numa_blacklist_ids == null) ? [] : data.fsb_numa_blacklist_ids.replace('{', '').replace('}', '').split(',');
    $scope.item.fsb_numb_blacklist_ids = (data.fsb_numb_blacklist_ids == null) ? [] : data.fsb_numb_blacklist_ids.replace('{', '').split(',');

    $scope.item.trunkRulesOrigination = $scope.item.trunkRulesOrigination || [];
    $scope.item.trunkRulesTermination = $scope.item.trunkRulesTermination || [];
    $scope.item.corm_orig = $scope.item.corm_orig !== null ? $scope.item.corm_orig : false;
    $scope.item.corm_term = $scope.item.corm_term !== null ? $scope.item.corm_term : false;
    $scope.item.telemetry_receiver_id = $scope.item.telemetry_receiver_id !== null ? $scope.item.telemetry_receiver_id : false;
    $scope.item.dvoRules = $scope.item.dvoRules || [];
    $scope.item.dvo_default_action = $scope.item.dvo_default_action !== null ? $scope.item.dvo_default_action : false;
    $scope.item.call_telemetry_receiver_orig = data.call_telemetry_receiver_orig;
    $scope.item.call_telemetry_receiver_term = data.call_telemetry_receiver_term;
    $scope.item.dvo_telemetry_receiver = data.dvo_telemetry_receiver;
    $scope.item.dvo_enable_default = data.dvo_enable_default;
    $scope.item.epvvTransitRulesOrigination = $scope.item.epvvTransitRulesOrigination || [];
    $scope.item.epvvTransitRulesTermination = $scope.item.epvvTransitRulesTermination || [];

    if (data.epvvTransitRules) {
      data.epvvTransitRules.forEach(function(rule) {
          if (rule.is_orig) {
              $scope.item.epvvTransitRulesOrigination.push(rule);
          } else {
              $scope.item.epvvTransitRulesTermination.push(rule);
          }
      });
    }

    if ($scope.item.ast_trunk_group_id || $scope.item.ast_outcome_id) {
      $scope.vpbx_type_id = 2;
    } else {
      $scope.vpbx_type_id = 1;
    }

    if ($scope.item) {
      $scope.item.directionOptions = [
        { value: 'DIR_TX', label: 'DIR_TX' },
        { value: 'DIR_RX', label: 'DIR_RX' },
        { value: 'DIR_DX', label: 'DIR_DX' },
        { value: 'DIR_LX', label: 'DIR_LX' }
      ];
    }
  });

  List.prefixlist().then(function (data) {
    $scope.prefixlist_list = data;
  });

  List.mvnoPartner().then(function (data) {
    $scope.mvno_partner_list = data;
  });

  List.adapter($scope.server_id).then(function (data) {
    $scope.adapters = data;
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
    $scope.item.trunkRulesOrigination.forEach(function(rule) {
      if (!rule.direction) {
          rule.direction = 'DIR_TX';
      }
  });

    $scope.item.epvvTransitRules = $scope.item.epvvTransitRulesOrigination.concat($scope.item.epvvTransitRulesTermination);

    Settings.save($scope.item).then(function (response) {
      if ($scope.name_changed) {
        $window.location.reload();
      } else {
        $modalInstance.close();
      }
    });
  };

  $scope.addTrunkRuleOrigination = function () {
    $scope.item.trunkRulesOrigination.push({
        trunk_group_id: '',
        allow: $scope.item.corm_orig,
        is_orig: true,
        ac_mode: 0,
        direction: 'DIR_TX'
    });
};

  $scope.addEpvvTransitRuleOrigination = function () {
    $scope.item.epvvTransitRulesOrigination.push({
        trunk_group_id: '',
        allow: $scope.item.epvv_transit_orig,
        is_orig: true,
        number_id_filter_a: null,
        number_id_filter_b: null,
        number_id_filter_c: null,
        ac_mode: 0
    });
  };

  $scope.removeEpvvTransitRuleOrigination = function (index) {
    $scope.item.epvvTransitRulesOrigination.splice(index, 1);
  };

  $scope.addEpvvTransitRuleTermination = function () {
    $scope.item.epvvTransitRulesTermination.push({
        trunk_group_id: '',
        allow: $scope.item.epvv_transit_term,
        is_orig: false,
        number_id_filter_a: null,
        number_id_filter_b: null,
        number_id_filter_c: null,
        ac_mode: 0
    });
  };

  $scope.removeEpvvTransitRuleTermination = function (index) {
    $scope.item.epvvTransitRulesTermination.splice(index, 1);
  };

  $scope.addDvoRule = function () {
    $scope.item.dvoRules.push({
        allow: $scope.item.dvo_default_action,
        number_id_filter_a: null,
        telemetry_receiver_id: null,
        object_comment: ''
    });
  };

  $scope.removeDvoRule = function (index) {
      $scope.item.dvoRules.splice(index, 1);
  };

  $scope.addTrunkRuleTermination = function () {
    $scope.item.trunkRulesTermination.push({
        trunk_group_id: '',
        allow: $scope.item.corm_term, 
        is_orig: false,
        ac_mode: 0,
        direction: 'DIR_TX' 
    });
  };

  $scope.removeTrunkRuleOrigination = function (index) {
    $scope.item.trunkRulesOrigination.splice(index, 1);
  };

  $scope.removeTrunkRuleTermination = function (index) {
    $scope.item.trunkRulesTermination.splice(index, 1);
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
    $scope.item.mvno_link.push({
      number_capacity: [],
      mvno_trunk_ids: [],
      trunk_groups: [],
      ported_number_prefixes: [],
      excluded_number_prefixes: [],
      routing_number: ''
    });
  };

  $scope.removeMvnoLink = function (index) {
    $scope.item.mvno_link.splice(index, 1);
  };
};
