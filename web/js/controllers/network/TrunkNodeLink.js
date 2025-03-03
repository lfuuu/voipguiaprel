var TrunkNodeLinkListCtrl = function($scope, TrunkNodeLink, Node, Redirect, $window, $rootScope) {
    $scope.links = [];
    $scope.sortType = 'trunk_node_link_id';
    $scope.sortReverse = false;
    $scope.filterObj = {};
    $rootScope.userName = userName;
    $rootScope.userId = userId;
    $scope.baseLink = ($window.location.hostname).includes('.tech') ? 'https://stat.kompaas.tech/' : 'https://stat.mcn.ru/';

    $scope.customFilter = function(item) {
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
          var valueStr = item.service_trunk_id.toString();
          if (valueStr.indexOf(filterStr) === -1) {
            return false;
          }
        }
        return true;
      };
      

      $scope.init = function() {
        TrunkNodeLink.read().then(function(data) {
            $scope.links = data;
            // Формируем уникальный список типов контрактов из данных
            var types = {};
            angular.forEach(data, function(link) {
                // Если contract_type_text не пуст и еще не добавлено
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
        console.log(link.trunk_node_link_id)
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
};

app.controller('TrunkNodeLinkListCtrl', TrunkNodeLinkListCtrl);
