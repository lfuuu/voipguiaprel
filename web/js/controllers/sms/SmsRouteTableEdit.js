var SmsRouteTableEditCtrl = function(
  $rootScope,
  $scope,
  Redirect,
  SmsRouteTable,
  SmsList,
  params,
  $modalInstance,
  Number
) {

  // Если редактируем — подгружаем существующую запись
  if (params.id) {
    SmsRouteTable.get({ id: params.id }).then(function(data) {
      console.log(data);
      $scope.item = data;
    });
  } else {
    // Новый — жестко ставим server_id = 9
    $scope.item = {
      name: '',
      server_id: 9,
      routes: []
    };
  }

  // Параметры перетаскивания
  $scope.sortableOptions = {
    update: function(e, ui) {
      var sortBlocked = false;
      var idx    = ui.item.sortable.index;
      var dropIx = ui.item.sortable.dropindex;
      var dropMin = Math.min(idx, dropIx);
      var dropMax = Math.max(idx, dropIx);

      // Не даём двигать заблокированные маршруты
      for (var i = dropMin; i <= dropMax; i++) {
        if ($scope.item.routes[i] && $scope.item.routes[i].is_locked) {
          sortBlocked = true;
          break;
        }
      }
      if (sortBlocked) {
        ui.item.sortable.cancel();
      }
    },
    axis: 'y'
  };

  // Добавить новую строку маршрута
  $scope.addRoute = function() {
    $scope.item.routes.push({ a_number_id: null });
  };

  // Удалить строку маршрута
  $scope.removeRoute = function(index) {
    $scope.item.routes.splice(index, 1);
  };

  // Открыть лог действий
  $scope.viewActionLog = function() {
    if (!$scope.item.id) return;
    Redirect.actionLogView(
      $scope.item.id,
      'json/sms/sms-route-table',
      'save'
    );
  };

  // Сохранение
  $scope.save = function() {
    // Копируем, чтобы не мутировать исходный объект
    var data = angular.copy($scope.item);
    // server_id уже захардкожен, ничего не правим
    SmsRouteTable.save(data).then(function(res) {
      $modalInstance.close();
    });
  };

  // Закрыть без сохранения
  $scope.back = function() {
    $modalInstance.dismiss();
  };
};
