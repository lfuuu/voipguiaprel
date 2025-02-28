var TrunkNodeLinkListCtrl = function($scope, TrunkNodeLink, Node, Redirect, $window) {
    $scope.links = [];
    $scope.sortType = 'trunk_node_link_id';
    $scope.sortReverse = false;
    $scope.filterObj = {};

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
