var TrunkNodeLinkEditCtrl = function($rootScope, $scope, Link, Node, params, $modalInstance, $window, TrunkNodeLink) {
  Node.listNodesForLink().then(function(data) { $scope.nodesList = data; });

  var ss7RequestId = 0;

  function refreshSs7ByServiceTrunkId(serviceTrunkId) {
    if (!$scope.item) {
      return;
    }

    if (!serviceTrunkId) {
      $scope.item.ss7 = null;
      return;
    }

    ss7RequestId += 1;
    var requestId = ss7RequestId;

    TrunkNodeLink.getSs7({ service_trunk_id: serviceTrunkId }).then(function(data) {
      if (requestId !== ss7RequestId || !$scope.item) {
        return;
      }

      $scope.item.ss7 = data && data.ss7 ? data.ss7 : null;
    });
  }

  if (params.trunk_node_link_id !== null && params.trunk_node_link_id !== undefined && params.trunk_node_link_id !== '') {
    TrunkNodeLink.get({ id: params.trunk_node_link_id }).then(function(data) {
      $scope.item = data;
    });
  } else {
    $scope.item = {
      service_trunk_id: '',
      node_id: '',
      comment: '',
      ss7: null
    };
  }

  $scope.$watch('item.service_trunk_id', function(newVal, oldVal) {
    if (!$scope.item) {
      return;
    }

    if (newVal === oldVal && oldVal !== undefined) {
      return;
    }

    refreshSs7ByServiceTrunkId(newVal);
  });

  $scope.save = function() {
    TrunkNodeLink.save($scope.item).then(function() {
      $modalInstance.close();
    });
  };

  $scope.back = function() { $modalInstance.dismiss(); };
};
app.controller('TrunkNodeLinkEditCtrl', TrunkNodeLinkEditCtrl);
