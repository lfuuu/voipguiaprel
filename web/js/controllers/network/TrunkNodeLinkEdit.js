var TrunkNodeLinkEditCtrl = function($rootScope, $scope, Link, Node, params, $modalInstance, $window, TrunkNodeLink) {
  Node.listNodesForLink().then(function(data) { $scope.nodesList = data; });

  if (params.trunk_node_link_id !== null && params.trunk_node_link_id !== undefined && params.trunk_node_link_id !== '') {
    TrunkNodeLink.get({ id: params.trunk_node_link_id }).then(function(data) {
      $scope.item = data;
    });
  } else {
    $scope.item = {
      src_node_id: '',
      dst_node_id: '',
      weight: 1,
      comment: '',
      sorm_name: 'ООО МСН Телеком',
      sorm_id: ''
    };
  }

  $scope.save = function() {
    TrunkNodeLink.save($scope.item).then(function() {
      $modalInstance.close();
    });
  };

  $scope.back = function() { $modalInstance.dismiss(); };
};
app.controller('TrunkNodeLinkEditCtrl', TrunkNodeLinkEditCtrl);
