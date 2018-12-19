var TestNumberEditCtrl = function($scope, TestPricelist, Redirect, params, $modalInstance) {
    $scope.params = {
        number: '',
        weak_matching: false
    };

    $scope.processNumber = function () {
        TestPricelist.numberResult({number: $scope.params.number, weak_matching: $scope.params.weak_matching, server_id: $scope.server.id}).then(function (data) {
            $scope.item = data;
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};