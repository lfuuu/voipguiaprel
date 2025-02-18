var NodesListCtrl = function($scope, Node, Redirect, $window) {
  $scope.sortType = 'node_name_id';
  $scope.sortReverse = false;
  $scope.searchQuery = '';
  $scope.filterObj = {};
  $scope.filterFields = ['node_id', 'node_name_id', 'node_type_id', 'region_id'];

  // Кастомный фильтр для таблицы
  $scope.customFilter = function(item) {
    // Если filterObj пустой, возвращаем true (отображаем все элементы)
    if (!$scope.filterObj) return true;
    for (var key in $scope.filterObj) {
      if ($scope.filterObj.hasOwnProperty(key) && $scope.filterObj[key]) {
        // Приводим значение элемента и значение фильтра к строке в нижнем регистре
        var itemValue = (item[key] !== undefined && item[key] !== null) ? item[key].toString().toLowerCase() : "";
        var filterValue = $scope.filterObj[key].toString().toLowerCase();
        if (itemValue.indexOf(filterValue) === -1) {
          return false;
        }
      }
    }
    return true;
  };

  $scope.init = function(tab) {
    console.log('init вызывается');
    if (tab) {
      tab.title = 'Список узлов';
    }
    // Загрузка списка узлов
    Node.read().then(function(data) {
      $scope.list = data;
    });
    // Загрузка данных для выпадающих списков
    Node.nodeTypes().then(function(data) {
      $scope.nodeTypes = data;
    });
    Node.nodeStatuses().then(function(data) {
      $scope.nodeStatuses = data;
    });
    Node.russianDistricts().then(function(data) {
      $scope.russianDistricts = data;
    });
    Node.russianSubjects().then(function(data) {
      $scope.russianSubjects = data;
    });
    Node.russianCities().then(function(data) {
      $scope.russianCities = data;
    });
  };
  $scope.init();

  $scope.clickCreate = function() {
    Redirect.nodeCreate().then(function() {
      $scope.init();
    });
  };

  $scope.clickItem = function(item) {
    if (!userPermissions['network_list']) {
      return;
    }
    if (window.getSelection().type === 'Range') {
      return;
    }
    Redirect.nodeEdit(item.node_id).then(function() {
      $scope.init();
    });
  };

  $scope.deleteItem = function(item) {
    if (!userPermissions['network_edit']) {
      return;
    }
    if (!$window.confirm('Удалить?')) {
      return;
    }
    Node.delete(item.node_id).then(function(response) {
      $scope.init();
    });
  };
};
