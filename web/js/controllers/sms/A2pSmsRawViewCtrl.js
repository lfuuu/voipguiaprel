var A2pSmsRawViewCtrl = function($scope, $modalInstance, $http, params, $sce) {
    // Из параметров получаем cdr_id, для которого ищем A2P SMS данные
    $scope.cdrId = params.cdr_id;
    $scope.a2pSmsDetails = [];
    $scope.errorMessage = null;

    // Выполняем GET-запрос по URL, соответствующему маршруту контроллера
    $http.get('/json/a2p_sms/a2p-sms-raw/raw', { params: { cdr_id: $scope.cdrId } })
         .then(function(response) {
             $scope.a2pSmsDetails = response.data;
         })
         .catch(function(error) {
             $scope.errorMessage = "Ошибка: " + (error.data.error || error.statusText);
         });
    
    // Функция закрытия модального окна
    $scope.back = function() {
        $modalInstance.dismiss();
    };
};
