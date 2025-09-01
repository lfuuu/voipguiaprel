var SmsTrunkListCtrl = function($scope, SmsTrunk, Redirect, $window) {

  $scope.sortType = 'name';
  $scope.sortReverse = false;
  $scope.searchQuery = '';

  $scope.filterFields = [
    'id', 'name', 'server_id', 'a2psms_route_table_id', 'route_name',
    'connector_type_id', 'display_protocol', 'display_host_url',
    'display_port_method', 'display_user_ct'
  ];

  $scope.init = function(tab) {
    if (tab) tab.title = 'SMS trunks';

    // 1) тянем список коннекторов
    SmsTrunk.read({ server_id: $scope.server.id }).then(function(data){
      $scope.list = data || [];

      // 2) параллельно тянем список внешних конфигов
      return Promise.all([
        SmsTrunk.getSmppConfig(), // [{id,name,host,port,'smsc-username','smsc-password',...}]
        SmsTrunk.getApiConfig()   // [{id,name,url,method,contentType,'autorization-token',...}]
      ]);
    }).then(function(results){
      var smppList = results[0] || [];
      var apiList  = results[1] || [];

      // индексы по name для быстрого сопоставления
      var smppByName = {};
      smppList.forEach(function(x){ if (x && x.name) smppByName[x.name] = x; });

      var apiByName = {};
      apiList.forEach(function(x){ if (x && x.name) apiByName[x.name] = x; });

      // 3) обогащаем каждый item полями для отображения
      ($scope.list || []).forEach(function(item){
        // протокол: по твоей схеме id=1 smpp, id=2 rest
        var proto = (item.connector_proto_id === 1) ? 'SMPP'
                   : (item.connector_proto_id === 2) ? 'REST'
                   : null;

        item.display_protocol = proto;

        if (proto === 'SMPP') {
          var s = smppByName[item.name];
          if (s) {
            item._config_id = s.id; // может пригодиться
            item.display_host_url    = s.host || '';
            item.display_port_method = s.port || '';
            item.display_user_ct     = s['smsc-username'] || '';
          } else {
            item.display_host_url    = '';
            item.display_port_method = '';
            item.display_user_ct     = '';
          }
        } else if (proto === 'REST') {
          var a = apiByName[item.name];
          if (a) {
            item._config_id = a.id;
            item.display_host_url    = a.url || '';
            item.display_port_method = a.method || '';
            item.display_user_ct     = a.contentType || '';
          } else {
            item.display_host_url    = '';
            item.display_port_method = '';
            item.display_user_ct     = '';
          }
        } else {
          item.display_host_url    = '';
          item.display_port_method = '';
          item.display_user_ct     = '';
        }
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
    if (window.getSelection().type == 'Range') return;
    Redirect.smsRouteTableEdit(routeTableId).then(function () { $scope.init(); });
  };

  $scope.deleteItem = function(item) {
    if (!$window.confirm('Удалить?')) return;
    SmsTrunk.delete(item.id).then(function() { $scope.init(); });
  };
};
