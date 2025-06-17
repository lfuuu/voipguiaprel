var BillingServerEditCtrl = function($scope, BillingServer, params, $modalInstance) {
  if (params.id) {
    BillingServer.get({ id: params.id }).then(function(data) {
      $scope.item = data;
    });
  } else {
    $scope.item = {
      name: '',
      ip: '',
      contact_info: '',
      interface_url: '',
      dashboards: '',
      antifraud_incoming_accept: false,
      antifraud_proxy_timeout: 0,
      antifraud_proxy_timeout_prefixlist_id: null
    };
  }

  $scope.save = function() {
    BillingServer.save($scope.item).then(function(res) {
      $modalInstance.close(res);
    });
  };

  $scope.back = function() {
    $modalInstance.dismiss();
  };
};
