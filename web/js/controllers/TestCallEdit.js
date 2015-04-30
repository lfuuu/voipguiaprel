var TestCallEditCtrl = function($scope, TestCall, params, $modalInstance, $window) {

    if (params.id) {
        TestCall.get({id: params.id}).then(function(data){
            $scope.item = data;
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id,
            orig: true
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