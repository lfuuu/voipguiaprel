var NodesListCtrl = function($scope, Node, Redirect, $window) {
  $scope.sortType = 'district_text';
  $scope.sortReverse = true;
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
    if (tab) {
      tab.title = 'Список узлов';
    }
    Node.read().then(function(data) {
      data.sort(function(a, b) {
        return ('' + a.node_name_id).localeCompare('' + b.node_name_id);
      });
      $scope.list = data;
    });

    Node.nodeTypes().then(function(data) {
      data.sort(function(a, b) {
        return a.node_type.localeCompare(b.node_type);
      });
      $scope.nodeTypes = data;
    });
    
    Node.nodeStatuses().then(function(data) {
      data.sort(function(a, b) {
        return a.node_status.localeCompare(b.node_status);
      });
      $scope.nodeStatuses = data;
    });
    
    Node.russianDistricts().then(function(data) {
      data.sort(function(a, b) {
        return a.russian_district.localeCompare(b.russian_district);
      });
      $scope.russianDistricts = data;
    });
    
    Node.russianSubjects().then(function(data) {
      data.sort(function(a, b) {
        return a.russian_subject.localeCompare(b.russian_subject);
      });
      $scope.russianSubjects = data;
    });
    
    Node.russianCities().then(function(data) {
      data.sort(function(a, b) {
        return a.russian_city.localeCompare(b.russian_city);
      });
      $scope.russianCities = data;
    });
    
    Node.serverList().then(function(data) {
      data.sort(function(a, b) {
        return a.name.localeCompare(b.name);
      });
      $scope.serverList = data;
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
