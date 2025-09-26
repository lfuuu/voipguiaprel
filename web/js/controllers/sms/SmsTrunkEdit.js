var SmsTrunkEditCtrl = function($scope, SmsTrunk, SmsList, params, $modalInstance) {
  $scope.AGG_ID = null;

  $scope.smpp = {
    system_type: '',   // строка или '' (не указывать)
    use_ssl: false     // boolean
  };
  $scope.rest = {};

  $scope.gatewayList       = [];
  $scope.connectorTypeList = [];
  $scope.protocolList      = [];
  $scope.routeTableList    = [];

  SmsList.gateways().then(function(gates) {
    $scope.gatewayList = gates.map(function(g){
      return { id: g.id, name: g.name, type: g.type };
    });
    initItem();
  });

  function findGate(id) {
    return $scope.gatewayList.find(function(g){ return g.id === id; }) || null;
  }

  function recalcFlags() {
    var gate = findGate($scope.item.sms_gate_id);
    $scope.selectedGate = gate;
    $scope.isMCMCN      = !!(gate && gate.type === 'MCMCN');
  }

  function initItem() {
    if (!params.id) {
      $scope.item = {
        name: '',
        route_name: '',
        sms_gate_id: null,
        connector_type_id: null,
        connector_proto_id: null,
        a2psms_route_table_id: null
      };
      SmsList.routeTable({ server_id: 9 }).then(function(rt){ $scope.routeTableList = rt; });
      setupWatchers();
      recalcFlags();
      return;
    }

    SmsTrunk.get({ id: params.id })
      .then(function(data) {
        $scope.item = {
          id:                      data.id,
          name:                    data.name,
          route_name:              data.route_name,
          sms_gate_id:             data.sms_gate_id,
          connector_type_id:       data.connector_type_id,
          connector_proto_id:      data.connector_proto_id,
          a2psms_route_table_id:   data.a2psms_route_table_id
        };
        recalcFlags();
        return SmsList.routeTable({ server_id: data.server_id });
      })
      .then(function(rt) {
        $scope.routeTableList = rt;
        return SmsList.connectorTypes({ sms_gate_id: $scope.item.sms_gate_id });
      })
      .then(function(types) {
        $scope.connectorTypeList = types;
        types.forEach(function(t){ if (t.type === 'agregat') $scope.AGG_ID = t.id; });
        if ($scope.isMCMCN && $scope.item.connector_type_id === $scope.AGG_ID) {
          return SmsList.connectorProtos().then(function(plist){ $scope.protocolList = plist; });
        }
      })
      .finally(function(){
        setupWatchers();
        loadExistingConfig();
      });
  }

  function setupWatchers() {
    $scope.$watch('item.sms_gate_id', function(gateId) {
      recalcFlags();
      if (!gateId) {
        $scope.connectorTypeList = [];
        $scope.AGG_ID = null;
        $scope.item.connector_type_id = null;
        return;
      }
      SmsList.connectorTypes({ sms_gate_id: gateId }).then(function(list){
        $scope.connectorTypeList = list;
        list.forEach(function(t){ if (t.type === 'agregat') $scope.AGG_ID = t.id; });
      });
    });

    $scope.$watchGroup(['item.sms_gate_id','item.connector_type_id'], function() {
      recalcFlags();
      var isAgg = ($scope.item.connector_type_id === $scope.AGG_ID);
      if ($scope.isMCMCN && isAgg) {
        SmsList.connectorProtos().then(function(plist){
          $scope.protocolList = plist;
          loadExistingConfig();
        });
      } else {
        $scope.protocolList = [];
        $scope.item.connector_proto_id = null;
      }
    });

    $scope.$watch('item.connector_proto_id', loadExistingConfig);
  }

  function loadExistingConfig() {
    if (!$scope.protocolList.length || !$scope.item.connector_proto_id) return;
    var proto = $scope.protocolList.find(function(p){ return p.id === $scope.item.connector_proto_id; });
    if (!proto) return;

    if (proto.type === 'smpp') {
      SmsTrunk.getSmppConfig().then(function(list){
        var entry = list.find(function(e){ return e.name === $scope.item.name; });
        if (entry) {
          // базовые
          $scope.smpp.url  = entry.host || entry.url || '';
          $scope.smpp.port = entry.port || '';

          // username/password: поддерживаем camelCase и kebab-case
          $scope.smpp.smsc_username =
            entry['smsc-username'] != null ? entry['smsc-username'] :
            (entry.smscUsername != null ? entry.smscUsername : '');

          $scope.smpp.smsc_password =
            entry['smsc-password'] != null ? entry['smsc-password'] :
            (entry.smscPassword != null ? entry.smscPassword : '');

          // system-type: camelCase + kebab-case; пустое => ''
          var st = (entry['system-type'] != null ? entry['system-type'] : entry.systemType);
          $scope.smpp.system_type = (st == null ? '' : st);

          // use-ssl: camelCase + kebab-case -> boolean
          var ssl = (entry['use-ssl'] != null ? entry['use-ssl'] : entry.useSsl);
          $scope.smpp.use_ssl = !!ssl;
        }
      });

    } else if (proto.type === 'rest') {
      SmsTrunk.getApiConfig().then(function(list){
        var entry = list.find(function(e){ return e.name === $scope.item.name; });
        if (entry) {
          $scope.rest.url         = entry.url;
          $scope.rest.method      = entry.method;
          $scope.rest.contentType = entry.contentType;
          $scope.rest.authToken   = entry.autorizationToken;
        }
      });
    }
  }

  // сохранение (создание внешней конфигурации при необходимости)
  $scope.save = function() {
    $scope.item.route_name = $scope.item.name;
    SmsTrunk.save($scope.item).then(function(res){
      var trunkId  = res.id || $scope.item.id;
      var isAgg    = $scope.item.connector_type_id === $scope.AGG_ID;
      var protoObj = $scope.protocolList.find(function(p){ return p.id === $scope.item.connector_proto_id; });
      var pt       = protoObj && protoObj.type;

      // вместо сравнения по ID — опора на тип шлюза:
      if ($scope.isMCMCN && isAgg && pt === 'smpp') {
        var payload = {
          trunk_id:        trunkId,
          name:            $scope.item.name,
          host:            $scope.smpp.url,
          port:            $scope.smpp.port,
          'smsc-username': $scope.smpp.smsc_username,
          'smsc-password': $scope.smpp.smsc_password,
          'use-ssl':       !!$scope.smpp.use_ssl
        };
        // system-type отправляем ТОЛЬКО если непустой
        if ($scope.smpp.system_type && $scope.smpp.system_type.trim().length > 0) {
          payload['system-type'] = $scope.smpp.system_type.trim();
        }

        return SmsTrunk.addSmppConfiguration(payload)
          .then(function(){ $modalInstance.close(); });
      }

      if ($scope.isMCMCN && isAgg && pt === 'rest') {
        return SmsTrunk.addApiConfiguration({
          trunk_id:             trunkId,
          name:                 $scope.item.name,
          url:                  $scope.rest.url,
          method:               $scope.rest.method,
          contentType:          $scope.rest.contentType,
          'autorization-token': $scope.rest.authToken
        }).then(function(){ $modalInstance.close(); });
      }

      $modalInstance.close();
    });
  };

  function currentProtoType() {
    var p = $scope.protocolList.find(function(x){ return x.id === $scope.item.connector_proto_id; });
    return p ? p.type : null;
  }

  $scope.modifyConfig = function() {
    var t = currentProtoType();
    var trunkId = $scope.item.id;
    if (!trunkId) return;

    if (t === 'smpp') {
      var payload = {
        trunk_id:        trunkId,
        name:            $scope.item.name,
        host:            $scope.smpp.url,
        port:            $scope.smpp.port,
        'smsc-username': $scope.smpp.smsc_username,
        'smsc-password': $scope.smpp.smsc_password,
        'use-ssl':       !!$scope.smpp.use_ssl
      };
      if ($scope.smpp.system_type && $scope.smpp.system_type.trim().length > 0) {
        payload['system-type'] = $scope.smpp.system_type.trim();
      }

      return SmsTrunk.modifySmppConfiguration(payload);
    } else if (t === 'rest') {
      return SmsTrunk.modifyApiConfiguration({
        trunk_id:             trunkId,
        name:                 $scope.item.name,
        url:                  $scope.rest.url,
        method:               $scope.rest.method,
        contentType:          $scope.rest.contentType,
        'autorization-token': $scope.rest.authToken
      });
    }
  };

  $scope.deleteConfig = function() {
    var t = currentProtoType();
    var trunkId = $scope.item.id;
    if (!trunkId) return;
    if (!confirm('Удалить конфигурацию коннектора во внешнем сервисе?')) return;

    if (t === 'smpp') {
      return SmsTrunk.deleteSmppConfiguration({ trunk_id: trunkId });
    } else if (t === 'rest') {
      return SmsTrunk.deleteApiConfiguration({ trunk_id: trunkId });
    }
  };

  $scope.back = function(){ $modalInstance.dismiss(); };
};
