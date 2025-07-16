var SmsTrunkEditCtrl = function(
  $scope, SmsTrunk, SmsList, params, $modalInstance, $http, $q
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

      SmsTrunk.get({ id: params.id }).then(function(data) {
        data.sms_gate_id        = data.sms_gate_id;
        data.connector_type_id  = data.connector_type_id;
        data.connector_proto_id = data.connector_proto_id;
        $scope.item = data;

        SmsList.routeTable({ server_id: data.server_id }).then(function(rt) {
          $scope.routeTableList = rt;

          SmsList.connectorTypes({ sms_gate_id: data.server_id })
            .then(function(types) {
              $scope.connectorTypeList = types;
              types.forEach(function(t) {
                if (t.type === 'agregat') {
                  $scope.AGG_ID = t.id;
                }
              });

              if (
                $scope.item.sms_gate_id === $scope.MCMCN_ID &&
                $scope.item.connector_type_id === $scope.AGG_ID &&
                $scope.item.connector_proto_id
              ) {
                SmsList.connectorProtos().then(function(plist) {
                  $scope.protocolList = plist;
                });
              }

              setupWatchers();
            });
        });
      });
    }

    function setupWatchers() {
      $scope.$watch('item.sms_gate_id', function(gateId) {
        if (!gateId) {
          $scope.connectorTypeList = [];
          $scope.AGG_ID = null;
          $scope.item.connector_type_id = null;
          return;
        }
        SmsList.connectorTypes({ sms_gate_id: gateId })
          .then(function(types) {
            $scope.connectorTypeList = types;
            types.forEach(function(t) {
              if (t.type === 'agregat') {
                $scope.AGG_ID = t.id;
              }
            });
          });
      });

      $scope.$watchGroup(
        ['item.sms_gate_id','item.connector_type_id'],
        function(vals) {
          var gate = vals[0], type = vals[1];
          if (gate === $scope.MCMCN_ID && type === $scope.AGG_ID) {
            SmsList.connectorProtos().then(function(plist) {
              $scope.protocolList = plist;
            });
          } else {
            $scope.protocolList = [];
            $scope.item.connector_proto_id = null;
          }
        }
      );
    }

    $scope.save = function() {
      SmsTrunk.save($scope.item).then(function(res) {
        var trunkId = res.id || $scope.item.id;
        var isMC   = $scope.item.sms_gate_id === $scope.MCMCN_ID;
        var isAgg  = $scope.item.connector_type_id === $scope.AGG_ID;

        var protoObj = $scope.protocolList.find(function(p){
          return p.id === $scope.item.connector_proto_id;
        });
        var protoType = protoObj && protoObj.type;

        // SMPP
        if (isMC && isAgg && protoType === 'smpp') {
          return SmsTrunk.addSmppConfiguration({
            trunk_id:       trunkId,
            name:           $scope.item.name,
            host:           $scope.smpp.url,
            port:           $scope.smpp.port,
            'smsc-username': $scope.smpp.smsc_username,
            'smsc-password': $scope.smpp.smsc_password
          }).then(function() {
            $modalInstance.close();
          });
        }

        // REST
        if (isMC && isAgg && protoType === 'rest') {
          return SmsTrunk.addApiConfiguration({
            trunk_id:             trunkId,
            name:                 $scope.item.name,
            url:                  $scope.rest.url,
            method:               $scope.rest.method,
            contentType:          $scope.rest.contentType,
            'autorization-token': $scope.rest.authToken
          }).then(function() {
            $modalInstance.close();
          });
        }

        $modalInstance.close();
      });
    };

    $scope.back = function() {
      $modalInstance.dismiss();
    };
  }