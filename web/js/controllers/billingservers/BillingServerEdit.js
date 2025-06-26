var BillingServerEditCtrl = function($scope, BillingServer, Prefixlist, params, $modalInstance) {

  $scope.oldId = params.old_id;

  function loadEmergencyPrefixlists() {
    Prefixlist.listEmergency().then(function(data) {
      $scope.prefixlistList = data;
      console.log('EMERGENCY PREFIXLISTS:', $scope.prefixlistList);
    }, function(err) {
      console.error('Ошибка при получении аварийных префикс-листов', err);
      $scope.prefixlistList = [];
    });
  }

  if (params.id) {
    BillingServer.get({ id: params.id }).then(function(data) {
      $scope.item = data;
      loadEmergencyPrefixlists();
    });
  } else {
    $scope.item = {
      id:                                  null,
      name:                                '',
      ip:                                  '',
      contact_info:                       '',
      address:                             '',
      interface_url:                      '',
      dashboards:                         '',
      description:                        '',
      antifraud_incoming_accept:          false,
      antifraud_proxy_timeout:            false,
      antifraud_proxy_timeout_prefixlist_id: null
    };
    loadEmergencyPrefixlists();
  }

  $scope.save = function() {
    var payload = angular.extend({}, $scope.item, { old_id: $scope.oldId });
    BillingServer.save(payload).then(function(res) {
      $modalInstance.close(res);
    });
  };

  $scope.back = function() {
    $modalInstance.dismiss();
  };
};
