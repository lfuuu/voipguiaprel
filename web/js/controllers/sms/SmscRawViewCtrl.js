var SmscRawViewCtrl = function($scope, $modalInstance, $http, params, $sce) {
    // Получаем smpp_cdr_id для поиска raw SMS
    $scope.smscCdrId = params.smpp_cdr_id;
    $scope.smsDetails = [];
    $scope.errorMessage = null;

    // Выполняем запрос для получения данных из таблицы smsc_raw
    $http.get('/json/sms/smsc-raw/raw', { params: { smpp_cdr_id: $scope.smscCdrId } })
        .then(function(response) {
            // Записываем полученные данные в переменную для отображения
            $scope.smsDetails = response.data;
        })
        .catch(function(error) {
            $scope.errorMessage = 'Ошибка: ' + (error.data.error || error.statusText);
        });
    
    // Функция для закрытия модального окна
    $scope.back = function() {
        $modalInstance.dismiss();
    };
};
