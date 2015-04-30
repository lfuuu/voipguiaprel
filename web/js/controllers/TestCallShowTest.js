var TestCallShowTestCtrl = function($scope, TestCall, params, $modalInstance, $window) {

    if (params.id) {
        TestCall.result({id: params.id}).then(function(data){
            $scope.item = data.item;
            $scope.result = data.result;
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id
        };
    }

    $scope.save = function()
    {
        TestCall.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    }
};