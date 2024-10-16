var CormAdapterListCtrl = function($scope, TelemetryReceiver, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'id', 'name'
    ];

    $scope.init = function() {
        TelemetryReceiver.list({ server_id: $scope.server.id }).then(function(response) {
            $scope.list = response;
        }, function(error) {
            console.error("Ошибка при загрузке списка адаптеров:", error);
        });
    };

    $scope.clickCreate = function() {
        Redirect.adapterCreate().then(function() {
            $scope.init();
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['corm_edit']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.adapterEdit(item.id).then(function() {
            $scope.init();
        });
    };

    $scope.deleteItem = function(item) {
        if (!$window.confirm('Удалить адаптер?')) return;

        TelemetryReceiver.delete({ id: item.id }).then(function(response) {
            $scope.init();
        });
    };

    $scope.init();
};
