var SmsTrunkListCtrl = function($scope, SmsTrunk, Redirect, $window, SmsList) {

  $scope.sortType = 'name';
  $scope.sortReverse = false;
  $scope.searchQuery = '';

  $scope.filterFields = [
    'id', 'name', 'server_id',
    'connector_type_name',
    'route_table_name',
    'route_name',
    'display_protocol', 'display_host_url',
    'display_port_method', 'display_user_ct'
  ];

  var routeTableById = {};
  var typeById       = {};
  var typesLoadedForGate = {};

  $scope.init = function(tab) {
    if (tab) tab.title = 'SMS trunks';

    SmsTrunk.read({ server_id: $scope.server.id }).then(function(data){
      $scope.list = data || [];

      return Promise.all([
        SmsTrunk.getSmppConfig(),
        SmsTrunk.getApiConfig(),
        SmsList.routeTable({ server_id: $scope.server.id })
      ]);
    }).then(function(results){
      var smppList = results[0] || [];
      var apiList  = results[1] || [];
      var routeList = results[2] || [];
      routeTableById = {};
      routeList.forEach(function(r){
        if (r && r.id != null) routeTableById[r.id] = r;
      });

      var gateIds = {};
      ($scope.list || []).forEach(function(x){
        if (x && x.sms_gate_id != null) gateIds[x.sms_gate_id] = true;
      });

      var gateIdArr = Object.keys(gateIds).map(function(k){ return +k; });
      var typePromises = gateIdArr
        .filter(function(gid){ return !typesLoadedForGate[gid]; })
        .map(function(gid){
          return SmsList.connectorTypes({ sms_gate_id: gid }).then(function(list){
            (list || []).forEach(function(t){ typeById[t.id] = t; });
            typesLoadedForGate[gid] = true;
          });
        });

      return Promise.all(typePromises).then(function(){
        var smppByName = {};
        smppList.forEach(function(x){ if (x && x.name) smppByName[x.name] = x; });

        var apiByName = {};
        apiList.forEach(function(x){ if (x && x.name) apiByName[x.name] = x; });

        ($scope.list || []).forEach(function(item){
          var t = typeById[item.connector_type_id];
          item.connector_type_name = t ? t.name : '';

          var rt = routeTableById[item.a2psms_route_table_id];
          item.route_table_name = rt ? rt.name : '';

          var proto = (item.connector_proto_id === 1) ? 'SMPP'
                     : (item.connector_proto_id === 2) ? 'REST'
                     : null;
          item.display_protocol = proto;

          if (proto === 'SMPP') {
            var s = smppByName[item.name];
            if (s) {
              item._config_id           = s.id;
              item.display_host_url     = s.host || '';
              item.display_port_method  = s.port || '';
              item.display_user_ct      = s['smsc-username'] || '';
            } else {
              item.display_host_url = item.display_port_method = item.display_user_ct = '';
            }
          } else if (proto === 'REST') {
            var a = apiByName[item.name];
            if (a) {
              item._config_id           = a.id;
              item.display_host_url     = a.url || '';
              item.display_port_method  = a.method || '';
              item.display_user_ct      = a.contentType || '';
            } else {
              item.display_host_url = item.display_port_method = item.display_user_ct = '';
            }
          } else {
            item.display_host_url = item.display_port_method = item.display_user_ct = '';
          }
        });
      });

    });
  };

  $scope.clickCreate = function() {
    Redirect.smsTrunkCreate().then(function () { $scope.init(); });
  };

  $scope.clickItem = function(item) {
    if (!userPermissions['sms_trunk_edit']) return;
    Redirect.smsTrunkEdit(item.id).then(function () { $scope.init(); });
  };

  $scope.openRouteTable = function(routeTableId) {
    if (!routeTableId) return;
    if (window.getSelection().type == 'Range') return;
    Redirect.smsRouteTableEdit(routeTableId).then(function () { $scope.init(); });
  };

  $scope.deleteItem = function(item) {
    if (!$window.confirm('Удалить?')) return;
    SmsTrunk.delete(item.id).then(function() { $scope.init(); });
  };

  $scope.init();
};
