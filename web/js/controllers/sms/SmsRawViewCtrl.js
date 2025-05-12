var SmsRawViewCtrl = function($scope, $modalInstance, $http, params, $sce) {
    // Получаем cdr_id для поиска raw SMS
    $scope.cdrId = params.cdr_id;
    $scope.smsDetails = [];
    $scope.errorMessage = null;

    // Запрос данных из нашего нового контроллера SmsRawController
    $http.get('/json/sms/sms-raw/raw', { params: { cdr_id: $scope.cdrId } })
        .then(function(response) {
            $scope.smsDetails = response.data;
        })
        .catch(function(error) {
            $scope.errorMessage = 'Ошибка: ' + (error.data.error || error.statusText);
        });

    // Закрыть модалку
    $scope.back = function() {
        $modalInstance.dismiss();
    };
};
