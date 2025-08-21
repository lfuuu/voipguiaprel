var SmsTrunkEditCtrl = function(
  $scope, SmsTrunk, SmsList, params, $modalInstance
) {
  $scope.MCMCN_ID = 2;
  $scope.AGG_ID   = null;

  $scope.smpp = {};
  $scope.rest = {};

  $scope.gatewayList       = [];
  $scope.connectorTypeList = [];
  $scope.protocolList      = [];
  $scope.routeTableList    = [];

  // 1) Загружаем шлюзы
  SmsList.gateways().then(function(gates) {
    $scope.gatewayList = gates.map(function(g){
      return { id: g.id, name: g.name };
    });
    initItem();
  });

  function initItem() {
    if (!params.id) {
      // новый — инициализируем пустой и сразу подгружаем routeTableList
      $scope.item = {
        name: '',
        route_name: '',
        sms_gate_id: null,
        connector_type_id: null,
        connector_proto_id: null,
        a2psms_route_table_id: null
      };
      // **захардкодил server_id = 9 для routeTable**
      SmsList.routeTable({ server_id: 9 }).then(function(rt){
        $scope.routeTableList = rt;
      });
      setupWatchers();
      return;
    }

    // редактируем существующий
    SmsTrunk.get({ id: params.id })
    .then(function(data) {
      // заполняем только нужные поля в item
      $scope.item = {
        id:                      data.id,
        name:                    data.name,
        route_name:              data.route_name,
        sms_gate_id:             data.sms_gate_id,
        connector_type_id:       data.connector_type_id,
        connector_proto_id:      data.connector_proto_id,
        a2psms_route_table_id:   data.a2psms_route_table_id
      };
      // а таблицу маршрутизации — по server_id из data.server_id
      return SmsList.routeTable({ server_id: data.server_id });
    })
    .then(function(rt) {
      $scope.routeTableList = rt;
      // теперь типы коннекторов
      return SmsList.connectorTypes({ sms_gate_id: $scope.item.sms_gate_id });
    })
    .then(function(types) {
      $scope.connectorTypeList = types;
      types.forEach(function(t){
        if (t.type === 'agregat') {
          $scope.AGG_ID = t.id;
        }
      });
      // если уже выбран MessageCenter+Агрегатор — подгрузим протоколы
      if (
        $scope.item.sms_gate_id === $scope.MCMCN_ID &&
        $scope.item.connector_type_id === $scope.AGG_ID
      ) {
        return SmsList.connectorProtos().then(function(plist){
          $scope.protocolList = plist;
        });
      }
    })
    .finally(function(){
      setupWatchers();
      loadExistingConfig();
    });
  }

  function setupWatchers() {
    // при смене шлюза — обновляем типы коннекторов
    $scope.$watch('item.sms_gate_id', function(gateId) {
      if (!gateId) {
        $scope.connectorTypeList = [];
        $scope.AGG_ID = null;
        $scope.item.connector_type_id = null;
        return;
      }
      SmsList.connectorTypes({ sms_gate_id: gateId })
        .then(function(list){
          $scope.connectorTypeList = list;
          list.forEach(function(t){
            if (t.type === 'agregat') {
              $scope.AGG_ID = t.id;
            }
          });
        });
    });

    // при смене шлюза или типа — подгружаем или сбрасываем протоколы
    $scope.$watchGroup(
      ['item.sms_gate_id','item.connector_type_id'],
      function(vals) {
        var gate = vals[0], type = vals[1];
        if (gate === $scope.MCMCN_ID && type === $scope.AGG_ID) {
          SmsList.connectorProtos().then(function(plist){
            $scope.protocolList = plist;
            loadExistingConfig();
          });
        } else {
          $scope.protocolList = [];
          $scope.item.connector_proto_id = null;
        }
      }
    );

    // при смене протокола — подтягиваем уже сохранённый конфиг
    $scope.$watch('item.connector_proto_id', loadExistingConfig);
  }

  function loadExistingConfig() {
    if (!$scope.protocolList.length || !$scope.item.connector_proto_id) return;
    var proto = $scope.protocolList.find(function(p){
      return p.id === $scope.item.connector_proto_id;
    });
    if (!proto) return;

    if (proto.type === 'smpp') {
      SmsTrunk.getSmppConfig().then(function(list){
        var entry = list.find(function(e){ return e.name === $scope.item.name; });
        if (entry) {
          $scope.smpp.url           = entry.host;
          $scope.smpp.port          = entry.port;
          $scope.smpp.smsc_username = entry['smsc-username'];
          $scope.smpp.smsc_password = entry['smsc-password'];
        }
      });
    }
    else if (proto.type === 'rest') {
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

  // сохранение
  $scope.save = function() {
    SmsTrunk.save($scope.item).then(function(res){
      var trunkId = res.id || $scope.item.id;
      var isMC    = $scope.item.sms_gate_id === $scope.MCMCN_ID;
      var isAgg   = $scope.item.connector_type_id === $scope.AGG_ID;
      var protoObj = $scope.protocolList.find(function(p){
        return p.id === $scope.item.connector_proto_id;
      });
      var pt = protoObj && protoObj.type;

      if (isMC && isAgg && pt === 'smpp') {
        return SmsTrunk.addSmppConfiguration({
          trunk_id:        trunkId,
          name:            $scope.item.name,
          host:            $scope.smpp.url,
          port:            $scope.smpp.port,
          'smsc-username': $scope.smpp.smsc_username,
          'smsc-password': $scope.smpp.smsc_password
        }).then(function(){
          $modalInstance.close();
        });
      }

      if (isMC && isAgg && pt === 'rest') {
        return SmsTrunk.addApiConfiguration({
          trunk_id:             trunkId,
          name:                 $scope.item.name,
          url:                  $scope.rest.url,
          method:               $scope.rest.method,
          contentType:          $scope.rest.contentType,
          'autorization-token': $scope.rest.authToken
        }).then(function(){
          $modalInstance.close();
        });
      }

      // ни один внешний API не нужен — просто закрываем
      $modalInstance.close();
    });
  };

  $scope.back = function(){
    $modalInstance.dismiss();
  };
};
