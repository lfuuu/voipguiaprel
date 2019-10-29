var CamelSettingsEditCtrl = function ($scope, $window, CamelSettings, List, $modalInstance, params) {
    $scope.title = 'Общие настройки';
    $scope.old_name = '';

    CamelSettings.get({id: $scope.server.id}).then(function (data) {
        $scope.item = data;
        $scope.old_name = data.name;
    });

    List.server().then(function (data) {
        $scope.serverList = data;
    });

    $scope.save = function () {
        CamelSettings.save($scope.item).then(function (response) {
            if ($scope.old_name !== $scope.item.name || $scope.item.default_routing_server_id !== "" + $scope.server.default_routing_server_id) {
                $window.location.reload();
            } else {
                $modalInstance.close();
            }
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
