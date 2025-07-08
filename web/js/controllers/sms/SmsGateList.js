var SmsGateListCtrl = function($scope, SmsGate, Redirect, $window) {
    $scope.sortType     = 'name';
    $scope.sortReverse  = false;
    $scope.searchQuery  = '';

    $scope.filterFields = [
        'id', 'name', 'description', 'ip', 'host'
    ];

    $scope.init = function(tab) {
        if (tab) tab.title = 'Узлы передачи данных';
        SmsGate.read().then(function(data) {
            $scope.list = data;
        });
    };

    $scope.clickCreate = function() {
        Redirect.smsGateCreate().then(function () {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['sms_trunk_edit']) {
            return;
        }
        Redirect.smsGateEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить?')) return;
        SmsGate.delete(item.id).then(function() {
            $scope.init();
        });
    };
};
