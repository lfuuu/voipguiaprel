var SmsTestAuthShowTestCtrl = function($scope, SmsTestAuth, params, $modalInstance, $window) {

    $scope.details = 1;

    if (params.id) {
        SmsTestAuth.result({id: params.id, is_reserve: params.is_reserve}).then(function(data){
            $scope.item = data.item;
            $scope.result = data.result;
            $scope.key = data.key;
            $scope.url = data.url;
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id
        };
    }

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};