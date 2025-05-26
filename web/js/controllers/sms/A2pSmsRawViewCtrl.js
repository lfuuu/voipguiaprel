var A2pSmsRawViewCtrl = function($scope, $modalInstance, $http, params) {
  // Получаем cdr_id для поиска raw A2P-SMS
  $scope.cdrId       = params.cdr_id;
  $scope.smsDetails  = [];
  $scope.errorMessage = null;
  $scope.loading     = true;

  // Запрос к нашему новому контроллеру
  $http.get('/json/sms/a2p-sms-raw/raw', {
    params: { cdr_id: $scope.cdrId }
  }).then(function(response) {
    $scope.smsDetails = response.data;
    $scope.loading    = false;
  }).catch(function(error) {
    $scope.errorMessage = 'Ошибка: ' + (error.data.error || error.statusText);
    $scope.loading      = false;
  });

  // Закрыть модалку
  $scope.close = function() {
    $modalInstance.dismiss();
  };
};
