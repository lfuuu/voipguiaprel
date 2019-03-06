var BlacklistSettingsEditCtrl = function($scope, BlacklistSettings, Prefixlist, $modalInstance) {
    $scope.title = 'Настройки фильтра по номеру А';

    $scope.resultNotExists = 0;
    $scope.resultSuccess = 1;
    $scope.resultExists = 2;
    $scope.resultDeleteSuccess = 3;
    $scope.resultLengthTooShort = 4;

    $scope.resultText = {
        0: 'Не существует',
        1: 'Успешно добавлено',
        2: 'Уже существует',
        3: 'Успешно удалено',
        4: 'Не добавлено: недостаточная длина префикса'
    };

    BlacklistSettings.get({server_id: $scope.server.id}).then(function(data) {
        $scope.itemA = data['a'];
        $scope.itemB = data['b'];
    });

    Prefixlist.listBlocked({server_id: $scope.server.id}).then(function(data) {
        $scope.prefixlist_block = data;
    });


    $scope.addA = function() {
        BlacklistSettings.add({id: $scope.itemA.id, prefixes: $scope.itemA.prefixes, shared_prefix: $scope.itemA.shared_prefix ? $scope.itemA.shared_prefix : ''}).then(function(response) {
            $scope.processResponse(response);
        });
    };

    $scope.deleteA = function() {
        BlacklistSettings.delete({id: $scope.itemA.id, prefixes: $scope.itemA.prefixes, shared_prefix: $scope.itemA.shared_prefix ? $scope.itemA.shared_prefix : ''}).then(function(response) {
            $scope.processResponse(response);
        });
    };

    $scope.checkA = function() {
        BlacklistSettings.check({id: $scope.itemA.id, prefixes: $scope.itemA.prefixes, shared_prefix: $scope.itemA.shared_prefix ? $scope.itemA.shared_prefix : ''}).then(function(response) {
            $scope.processResponse(response);
        });
    };

    $scope.addB = function() {
        BlacklistSettings.add({id: $scope.itemB.id, prefixes: $scope.itemB.prefixes, shared_prefix: $scope.itemB.shared_prefix ? $scope.itemB.shared_prefix : ''}).then(function(response) {
            $scope.processResponse(response);
        });
    };

    $scope.deleteB = function() {
        BlacklistSettings.delete({id: $scope.itemB.id, prefixes: $scope.itemB.prefixes, shared_prefix: $scope.itemB.shared_prefix ? $scope.itemB.shared_prefix : ''}).then(function(response) {
            $scope.processResponse(response);
        });
    };

    $scope.checkB = function() {
        BlacklistSettings.check({id: $scope.itemB.id, prefixes: $scope.itemB.prefixes, shared_prefix: $scope.itemB.shared_prefix ? $scope.itemB.shared_prefix : ''}).then(function(response) {
            $scope.processResponse(response);
        });
    };

    $scope.add = function(prefixlist) {
        BlacklistSettings.add({id: prefixlist.id, prefixes: prefixlist.prefixes, shared_prefix: prefixlist.shared_prefix ? prefixlist.shared_prefix : ''}).then(function(response) {
            $scope.processResponse(response);
        });
    };

    $scope.delete = function(prefixlist) {
        BlacklistSettings.delete({id: prefixlist.id, prefixes: prefixlist.prefixes, shared_prefix: prefixlist.shared_prefix ? prefixlist.shared_prefix : ''}).then(function(response) {
            $scope.processResponse(response);
        });
    };

    $scope.check = function(prefixlist) {
        BlacklistSettings.check({id: prefixlist.id, prefixes: prefixlist.prefixes, shared_prefix: prefixlist.shared_prefix ? prefixlist.shared_prefix : ''}).then(function(response) {
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