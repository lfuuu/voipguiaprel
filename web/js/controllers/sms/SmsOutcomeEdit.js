var SmsOutcomeEditCtrl = function($rootScope, $scope, Redirect, SmsOutcome, SmsList, params, $modalInstance) {
    if (params.id) {
        SmsOutcome.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            name: '',
            server_id: $scope.server.id
        };
    }

    $scope.outcomeTypeList = SmsList.outcomeType();

    $scope.save = function () {
        SmsOutcome.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};