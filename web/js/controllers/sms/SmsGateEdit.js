var SmsGateEditCtrl = function($rootScope, $scope, Redirect, SmsGate, params, $modalInstance) {
    if (params.id) {
        SmsGate.get({ id: params.id }).then(function(data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            name:        '',
            description: '',
            ip:          '',
            host:        '',
            type:        'MCMCN'
    }

    $scope.save = function() {
        SmsGate.save($scope.item).then(function() {
            $modalInstance.close();
        });
    };

    $scope.back = function() {
        $modalInstance.dismiss();
    };
};
