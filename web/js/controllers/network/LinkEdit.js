var NodeLinkEditCtrl = function($rootScope, $scope, Link, params, $modalInstance, $window) {
    if (params.node_link_id) {
      Link.get({ id: params.node_link_id }).then(function(data) {
        $scope.item = data;
      });
    } else {
      $scope.item = {
        src_node_id: '',
        dst_node_id: '',
        unidirect: true,
        weight: 1,
        trunk_name: '',
        comment: ''
      };
    }
  
    $scope.save = function() {
      Link.save($scope.item).then(function() {
        $modalInstance.close();
      });
    };
  
    $scope.back = function() {
      $modalInstance.dismiss();
    };
  };
  