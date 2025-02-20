var NodesLinkListCtrl = function($scope, Link, Node, Redirect, $window) {
    $scope.sortType = 'node_link_id';
    $scope.sortReverse = false;
  
    $scope.filterObj = {};
  
    $scope.customFilter = function(item) {
      if (!$scope.filterObj.node_id_selected) {
        return true;
      }
      var chosenId = parseInt($scope.filterObj.node_id_selected, 10);
      if (item.src_node_id === chosenId || item.dst_node_id === chosenId) {
        return true;
      }
      return false;
    };
  
    $scope.init = function(tab) {
      console.log('NodesLinkListCtrl init');
      if (tab) {
        tab.title = 'Список транков (node_link)';
      }
      Link.read().then(function(data) {
        $scope.list = data;
      });
      Node.listNodesForLink().then(function(data) {
        $scope.nodesList = data;
      });
    };
    $scope.init();

    $scope.clickCreate = function() {
      Redirect.linkCreate().then(function() {
        $scope.init();
      });
    };
  
    $scope.clickItem = function(item) {
      if (window.getSelection().type === 'Range') {
        return;
      }
      Redirect.linkEdit(item.node_link_id).then(function() {
        $scope.init();
      });
    };
  
    $scope.deleteItem = function(item) {
      if (!$window.confirm('Удалить?')) {
        return;
      }
      Link.delete(item.node_link_id).then(function(response) {
        $scope.init();
      });
    };
  
    $scope.clickSrcNode = function(nodeId) {
      Redirect.nodeEdit(nodeId);
    };
    $scope.clickDstNode = function(nodeId) {
      Redirect.nodeEdit(nodeId);
    };
  
    $scope.clickSearch = function() {
    };
  };
  