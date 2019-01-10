var TestNumberEditCtrl = function($scope, TestPricelist, Redirect, params, $modalInstance) {
    $scope.params = {
        number: ''
    };

    $scope.processNumber = function () {
        TestPricelist.numberResult({number: $scope.params.number, server_id: $scope.server.id}).then(function (data) {
            $scope.item = data;
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};