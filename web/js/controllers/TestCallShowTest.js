var TestCallShowTestCtrl = function($scope, TestCall, params, $modalInstance, $window) {

    $scope.details = 0;

    if (params.id) {
        TestCall.result({id: params.id}).then(function(data){
            $scope.item = data.item;
            $scope.result = data.result;
            $scope.isStageRowType = function (row) {
                return row.type == 'STAGE';
            };
            $scope.result_new = data.result_new;
            if ($scope.result_new == null) {
                $scope.details = 4;
            }
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