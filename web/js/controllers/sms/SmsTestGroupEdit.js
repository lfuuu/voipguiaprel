var SmsTestGroupEditCtrl = function($rootScope, $scope, Redirect, SmsTestGroup, params, $modalInstance) {
    if (params.id) {
        SmsTestGroup.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            name: ''
        };
    }

    $scope.save = function () {
        SmsTestGroup.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
