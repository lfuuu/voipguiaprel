var BlacklistSettingsEditCtrl = function($scope, BlacklistSettings, $modalInstance) {
    $scope.title = 'Настройки фильтра по номеру А';

    $scope.resultNotExists = 0;
    $scope.resultSuccess = 1;
    $scope.resultExists = 2;
    $scope.resultDeleteSuccess = 3;

    $scope.resultText = {
        0: 'Не существует',
        1: 'Успешно добавлено',
        2: 'Уже существует',
        3: 'Успешно удалено'
    };

    BlacklistSettings.get({server_id: $scope.server.id}).then(function(data){
        $scope.item = data;
    });


    $scope.add = function() {
        BlacklistSettings.add({id: $scope.item.id, prefixes: $scope.item.prefixes}).then(function(response) {
            $scope.processResponse(response);
        });
    };

    $scope.delete = function() {
        BlacklistSettings.delete({id: $scope.item.id, prefixes: $scope.item.prefixes}).then(function(response) {
            $scope.processResponse(response);
        });
    };

    $scope.check = function() {
        BlacklistSettings.check({id: $scope.item.id, prefixes: $scope.item.prefixes}).then(function(response) {
            $scope.processResponse(response);
        });
    };

    $scope.processResponse = function(response) {
        var resultText = '';
        for (var i in response.data.result) {
            resultText += 'Префикс ' + i + ': ' + $scope.resultText[response.data.result[i]] + '\n';
        }
        alert(resultText);
    };

    $scope.back = function() {
        $modalInstance.dismiss();
    };

};