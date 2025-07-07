var BillingServerEditCtrl = function($scope, BillingServer, Prefixlist, params, $modalInstance) {
  $scope.oldId = params.old_id;

  // Статический список типов
  $scope.serverTypes = [
    { value: 'billing_voip', label: 'Billing VOIP' },
    { value: 'ocslte',       label: 'OCS LTE'     },
    { value: 'billing_api',  label: 'Billing API' }
  ];

  // Список для select-box префикс-листов
  $scope.listEmergency = [];

  function loadEmergencyPrefixlists() {
    Prefixlist.listEmergency().then(function(data) {
      $scope.listEmergency = data;
    }, function(err) {
      $scope.listEmergency = [];
    });
  }

  if (params.id) {
    BillingServer.get({ id: params.id }).then(function(data) {
      $scope.item = data;
      loadEmergencyPrefixlists();
    });
  } else {
    $scope.item = {
      id:      null,
      name:    '',
      ip:      '',
      contact_info: '',
      address:       '',
      interface_url: '',
      dashboards:    '',
      description:   '',
      antifraud_incoming_accept:          false,
      antifraud_proxy_timeout:            false,
      antifraud_proxy_timeout_prefixlist_id: null,
      type: ''
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
