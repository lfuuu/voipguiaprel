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

      // нормализуем dashboards, если пришло строкой
      if (typeof $scope.item.dashboards === 'string') {
        if ($scope.item.dashboards.trim() === '') {
          $scope.item.dashboards = {};
        } else {
          try { $scope.item.dashboards = JSON.parse($scope.item.dashboards); }
          catch (e) { $scope.item.dashboards = {}; }
        }
      }

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
      dashboards:    {},        // << было '' — поставили пустой объект JSON
      description:   '',
      antifraud_incoming_accept:          false,
      antifraud_proxy_timeout:            false,
      antifraud_proxy_timeout_prefixlist_id: null,
      type: ''
    };
    loadEmergencyPrefixlists();
  }

  $scope.save = function() {
    // гарантируем корректный json для dashboards
    var dash = $scope.item.dashboards;
    if (dash === null || dash === undefined) {
      dash = null;
    } else if (typeof dash === 'string') {
      dash = dash.trim();
      if (dash === '') {
        dash = null;         // можно и {}, на усмотрение — БД всё равно имеет DEFAULT
      } else {
        try { dash = JSON.parse(dash); }
        catch (e) { dash = null; }
      }
    }
    // формируем payload без мутаций item
    var payload = angular.extend({}, $scope.item, {
      dashboards: dash,
      old_id: $scope.oldId
    });

    BillingServer.save(payload).then(function(res) {
      $modalInstance.close(res);
    });
  };

  $scope.back = function() {
    $modalInstance.dismiss();
  };
};
