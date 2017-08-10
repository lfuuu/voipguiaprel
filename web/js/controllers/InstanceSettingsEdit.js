var InstanceSettingsEditCtrl = function($scope, InstanceSettings, $modalInstance) {
    $scope.title = 'Дополнительные настройки';

    InstanceSettings.get({server_id: $scope.server.id}).then(function(data){
        $scope.item = data;
    });


    $scope.save = function()
    {
        InstanceSettings.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    };

};