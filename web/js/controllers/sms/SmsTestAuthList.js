var SmsTestAuthListCtrl = function($scope, SmsTestAuth, SmsList, SmsGate, Redirect, $window, SmsTrunk) {
  var SERVER_ID = 9;

  $scope.sortType    = 'name';
  $scope.sortReverse = false;
  $scope.searchQuery = '';

  $scope.searchArray = {
    id:       '',
    name:     '',
    group_id: '',
    trunk:    '',
    gate_id:  '',
    result:   ''
  };

  // добавил route_table_name для клиентского поиска
  $scope.filterFields = ['id','name','trunk_name','result','route_table_name'];

  $scope.testResultList = [
    { id: '',        name: 'Все тесты'  },
    { id: 'success', name: 'Успешные'   },
    { id: 'failure', name: 'Неуспешные' }
  ];

  $scope.trunkList     = [];
  $scope.trunkFiltered = [];

  function updateFilteredTrunks() {
    var gid = $scope.searchArray.gate_id;
    $scope.searchArray.trunk = '';

    if (!gid) {
      SmsTrunk.readByGate({})
        .then(function(data) {
          $scope.trunkList     = data;
          $scope.trunkFiltered = data;
          $scope.refreshList();
        })
        .catch(function() {
          $scope.trunkList     = [];
          $scope.trunkFiltered = [];
          $scope.refreshList();
        });
    } else {
      SmsTrunk.readByGate({ sms_gate_id: gid })
        .then(function(data) {
          $scope.trunkFiltered = data;
          $scope.refreshList();
        })
        .catch(function() {
          $scope.trunkFiltered = [];
          $scope.refreshList();
        });
    }
  }

  $scope.clickSearch = function($event) {
    if ($event && $event.preventDefault) $event.preventDefault(); // гасим submit формы
    $scope.refreshList();
  };

  SmsList.testGroup({}).then(function(data) {
    $scope.testGroupList = data;
  });

  $scope.init = function(tab) {
    if (tab) tab.title = 'Тесты маршрутизации';
    $scope.refreshList();

    SmsGate.read({ server_id: SERVER_ID })
      .then(function(data) {
        $scope.gateList = data;
      })
      .catch(function() {
        $scope.gateList = [];
      });
  };

  $scope.refreshList = function() {
    SmsTestAuth.read({
      server_id:    SERVER_ID,
      search_array: $scope.searchArray
    }).then(function(data) {
      // бек должен вернуть route_table_name и route_table_id
      $scope.list = data;
    });
  };

  $scope.$watch('searchArray.gate_id', function(newVal, oldVal) {
    if (newVal !== oldVal) {
      updateFilteredTrunks();
    }
  });

  $scope.clickCreate = function() {
    Redirect.smsTestAuthCreate().then($scope.init);
  };

  $scope.showTestPrimary = function(item) {
    $scope.showTestBasic(item, 'smsTestAuthShowTest');
  };

  $scope.showTestBasic = function(item, method) {
    if (window.getSelection().type === 'Range') return;
    Redirect[method](item.id).then($scope.init);
  };

  $scope.clickItem = function(item) {
    if (!userPermissions['sms_test_auth_edit']) return;
    if (window.getSelection().type === 'Range') return;
    Redirect.smsTestAuthEdit(item.id).then($scope.init);
  };

  $scope.deleteItem = function(item) {
    if (!$window.confirm('Удалить?')) return;
    SmsTestAuth.delete(item.id).then($scope.init);
  };

  $scope.getTestResultIcon = function(passed) {
    if (passed === true)  return 'passed.png';
    if (passed === false) return 'failed.png';
    return 'not_executed.png';
  };

  $scope.cloneTest = function(item) {
    if (!userPermissions['sms_test_auth_create']) return;
    if (window.getSelection().type === 'Range') return;
    Redirect.smsTestAuthClone(item.id).then($scope.init);
  };

  // открытие таблицы маршрутизации (как в экране коннекторов)
  $scope.openRouteTable = function(routeTableId) {
    if (!routeTableId) return;
    if (window.getSelection().type == 'Range') return;
    Redirect.smsRouteTableEdit(routeTableId).then($scope.init);
  };

  updateFilteredTrunks();
  $scope.init();
};
