var BillingServerEditCtrl = function($scope, BillingServer, params, $modalInstance) {
  // Сохраняем старый ID для actionSave
  $scope.oldId = params.old_id;

  // Загрузка при редактировании
  if (params.id) {
    BillingServer.get({ id: params.id }).then(function(data) {
      $scope.item = data;
    });
  } else {
    $scope.item = {
      id:         null,
      name:       '',
      ip:         '',
      contact_info: '',
      address:      '',
      interface_url: '',
      dashboards: '',
      antifraud_incoming_accept: false,
      antifraud_proxy_timeout:    false,
      antifraud_proxy_timeout_prefixlist_id: null
    };
  }

  // Сохраняем с учётом старого ID
  $scope.save = function() {
    // payload будет содержать оба: новый id и old_id
    var payload = angular.extend({}, $scope.item, { old_id: $scope.oldId });
    BillingServer.save(payload).then(function(res) {
      $modalInstance.close(res);
    });
  };

  $scope.back = function() {
    $modalInstance.dismiss();
  };
};
