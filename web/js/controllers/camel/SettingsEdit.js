var CamelSettingsEditCtrl = function ($scope, $window, CamelSettings, List, $modalInstance, params) {
    $scope.title = 'Общие настройки';
    $scope.old_name = '';

    CamelSettings.get({id: $scope.server.id}).then(function (data) {
        $scope.item = data;
        $scope.old_name = data.name;
    });

    $scope.save = function () {
        CamelSettings.save($scope.item).then(function (response) {
            if ($scope.old_name !== $scope.item.name) {
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
