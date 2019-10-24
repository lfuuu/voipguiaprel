var CamelOutcomeEditCtrl = function($rootScope, $scope, Redirect, CamelOutcome, params, $modalInstance) {
    if (params.id) {
        CamelOutcome.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            name: '',
            server_id: $scope.server.id
        };
    }

    $scope.save = function () {
        CamelOutcome.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
