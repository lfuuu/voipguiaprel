var TestAuthEditCtrl = function($scope, TestAuth, params, $modalInstance, $window) {

    if (params.id) {
        TestAuth.get({id: params.id}).then(function(data){
            $scope.item = data;
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id,
            src_noa: 3,
            dst_noa: 3,
            redirect_number: ''
        }
    }

    $scope.save = function()
    {
        TestAuth.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    }
};