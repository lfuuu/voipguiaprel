var TrunkNodeLinkListCtrl = function($scope, TrunkNodeLink, Node, Redirect, $window, $rootScope, $filter) {
  $scope.links = [];
  $scope.sortType = 'trunk_node_link_id';
  $scope.sortReverse = false;
  $scope.filterObj = {};

  $rootScope.userName = userName;
  $rootScope.userId = userId;

  $scope.baseLink = ($window.location.hostname).includes('.tech')
    ? 'https://stat.kompaas.tech/'
    : 'https://stat.mcn.ru/';

  // Переключатель режима отображения
  $scope.searchArray = {
    onlyPhysical: false // false = показываем все (по умолчанию)
  };

  $scope.setPhysicalFilter = function(value) {
    $scope.searchArray.onlyPhysical = value;
    // если есть clickSearch – вызовем, чтобы обновить внешние состояния/метрики; иначе фильтрация и так произойдёт
    if (typeof $scope.clickSearch === 'function') {
      $scope.clickSearch();
    }
  };

  // Кастомный фильтр по полям формы
  $scope.customFilter = function(item) {
    if ($scope.filterObj.contract_type_id !== undefined &&
        $scope.filterObj.contract_type_id !== null &&
        $scope.filterObj.contract_type_id !== '') {
      if (item.contract_type_id !== parseInt($scope.filterObj.contract_type_id, 10)) {
        return false;
      }
    }

    if ($scope.filterObj.node_id_selected !== undefined &&
        $scope.filterObj.node_id_selected !== null &&
        $scope.filterObj.node_id_selected !== '') {
      if (item.node_id !== parseInt($scope.filterObj.node_id_selected, 10)) {
        return false;
      }
    }

    if ($scope.filterObj.service_trunk_id !== undefined &&
        $scope.filterObj.service_trunk_id !== null &&
        $scope.filterObj.service_trunk_id !== '') {
      var filterStr = $scope.filterObj.service_trunk_id.toString();
      var valueStr = (item.service_trunk_id || '').toString();
      if (valueStr.indexOf(filterStr) === -1) {
        return false;
      }
    }

    if ($scope.filterObj.description !== undefined &&
        $scope.filterObj.description !== null &&
        $scope.filterObj.description !== '') {
      if (!item.description ||
          item.description.toLowerCase().indexOf($scope.filterObj.description.toLowerCase()) === -1) {
        return false;
      }
    }

    if ($scope.filterObj.contragent_name !== undefined &&
        $scope.filterObj.contragent_name !== null &&
        $scope.filterObj.contragent_name !== '') {
      if (!item.contragent_name ||
          item.contragent_name.toLowerCase().indexOf($scope.filterObj.contragent_name.toLowerCase()) === -1) {
        return false;
      }
    }

    if ($scope.filterObj.trunk_id !== undefined &&
        $scope.filterObj.trunk_id !== null &&
        $scope.filterObj.trunk_id !== '') {
      var val = (item.trunk_id || '').toString();
      if (val.indexOf($scope.filterObj.trunk_id.toString()) === -1) {
        return false;
      }
    }

    // Режим "только физические": отбрасываем записи без IP
    if ($scope.searchArray.onlyPhysical) {
      var ip = (item.ip_address || '').toString().trim();
      if (!ip) return false;
    }

    return true;
  };

  // Представление данных для ng-repeat:
  // 1) применяет customFilter
  // 2) при onlyPhysical = true выполняет дедупликацию по trunk_id
  $scope.getViewLinks = function() {
    var filtered = $filter('filter')($scope.links, $scope.customFilter);

    if ($scope.searchArray.onlyPhysical) {
      var seen = Object.create(null);
      var uniq = [];

      // Если нужно оставить "лучшую" запись среди дублей — можно отсортировать filtered здесь
      // например по trunk_node_link_id убыв., чтобы брать самую свежую: 
      // filtered = $filter('orderBy')(filtered, '-trunk_node_link_id');

      angular.forEach(filtered, function(item) {
        // пропускаем всё, у чего нет trunk_id
        if (item.trunk_id === undefined || item.trunk_id === null) return;

        var key = item.trunk_id.toString();
        if (!seen[key]) {
          seen[key] = true;
          uniq.push(item);
        }
      });

      return uniq;
    }

    return filtered;
  };

  // Инициализация данных
  $scope.init = function() {
    TrunkNodeLink.read().then(function(data) {
      $scope.links = data;

      // Собираем типы присоединений для селекта
      var types = {};
      angular.forEach(data, function(link) {
        if (link.contract_type_text !== null && link.contract_type_text !== undefined) {
          types[link.contract_type_id] = link.contract_type_text;
        }
      });
      $scope.contractTypes = [];
      angular.forEach(types, function(text, id) {
        $scope.contractTypes.push({ id: parseInt(id, 10), name: text });
      });
    });

    Node.listNodesForLink().then(function(data) {
      $scope.nodesList = data;
    });
  };

  $scope.init();

  $scope.clickItem = function(link) {
    if (window.getSelection().type === 'Range') {
      return;
    }
    console.log(link.trunk_node_link_id);
    Redirect.nodeLinkEdit(link.trunk_node_link_id).then(function() {
      $scope.init();
    });
  };

  $scope.clickCreate = function() {
    Redirect.nodeLinkCreate().then(function() {
      $scope.init();
    });
  };

  $scope.deleteItem = function(link) {
    if (!$window.confirm('Удалить?')) {
      return;
    }
    TrunkNodeLink.delete(link.trunk_node_link_id).then(function() {
      $scope.init();
    });
  };

  $scope.clickNode = function(nodeId) {
    Redirect.nodeEdit(nodeId);
  };

  $scope.clickTrunk = function(trunkId, serverId) {
    if (window.getSelection().type === 'Range') return;

    $rootScope.server = { id: serverId };

    Redirect.trunkEditByServer(trunkId, serverId)
      .then(function() {
        $scope.init();
      });
  };
};

app.controller('TrunkNodeLinkListCtrl', TrunkNodeLinkListCtrl);
