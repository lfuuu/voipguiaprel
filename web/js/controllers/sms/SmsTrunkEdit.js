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

      SmsList.gateways().then(function(gates) {
        $scope.gatewayList = gates.map(function(g){
          return { id: g.id, name: g.name };
        });
        initItem();
      });

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
          setupWatchers();
          return;
        }

        // редактируем
        SmsTrunk.get({ id: params.id })
        .then(function(data) {
          $scope.item = {
            id: data.id,
            name: data.name,
            route_name: data.route_name,
            sms_gate_id: data.sms_gate_id,
            connector_type_id: data.connector_type_id,
            connector_proto_id: data.connector_proto_id,
            a2psms_route_table_id: data.a2psms_route_table_id
          };
          // таблица маршрутизации
          return SmsList.routeTable({ server_id: data.server_id });
        })
        .then(function(rt) {
          $scope.routeTableList = rt;
          // типы коннекторов
          return SmsList.connectorTypes({ sms_gate_id: $scope.item.sms_gate_id });
        })
        .then(function(types) {
          $scope.connectorTypeList = types;
          types.forEach(function(t){
            if (t.type === 'agregat') {
              $scope.AGG_ID = t.id;
            }
          });
          // если уже выбран агрегатор на Message Center
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

      // навешиваем watch’еры
      function setupWatchers() {
        // смена шлюза → новые типы
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

        // смена шлюза или типа → новые/сброс протоколов
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

        // смена протокола → подгрузка существующего конфига
        $scope.$watch('item.connector_proto_id', loadExistingConfig);
      }

      // подтягиваем уже сохранённый внешний конфиг
      function loadExistingConfig() {
        var pid = $scope.item.connector_proto_id;
        if (!pid || !$scope.protocolList.length) return;

        var proto = $scope.protocolList.find(function(p){ return p.id === pid; });
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

          // SMPP
          if (isMC && isAgg && pt === 'smpp') {
            return SmsTrunk.addSmppConfiguration({
              trunk_id:       trunkId,
              name:           $scope.item.name,
              host:           $scope.smpp.url,
              port:           $scope.smpp.port,
              'smsc-username': $scope.smpp.smsc_username,
              'smsc-password': $scope.smpp.smsc_password
            }).then(function(){
              $modalInstance.close();
            });
          }

          // REST
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

          // иначе просто закрыть
          $modalInstance.close();
        });
      };

      $scope.back = function(){
        $modalInstance.dismiss();
      };
    }