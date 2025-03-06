var GlobalSettingsCtrl = function($rootScope, $scope, GlobalSettings, $modalInstance) {
    GlobalSettings.get().then(function(data) {
        $scope.settings = data;
    });

    $scope.onAntifraudErrorChange = function() {
        if ($scope.settings.antifraud_error_check) {
            GlobalSettings.callProcedure({ procedure: 'public.set_antifraud_error_accept' })
                .then(function(response) {
                });
        } else {

            GlobalSettings.callProcedure({ procedure: 'public.set_antifraud_error_manual' })
                .then(function(response) {
                });
        }
    };

    $scope.onAntifraudRejectChange = function() {
        if ($scope.settings.antifraud_reject_check) {
            GlobalSettings.callProcedure({ procedure: 'public.set_antifraud_reject_accept' })
                .then(function(response) {});
        } else {
            GlobalSettings.callProcedure({ procedure: 'public.set_antifraud_reject_manual' })
                .then(function(response) {});
        }
    };

    $scope.onAntifraudTimeoutChange = function() {
        if ($scope.settings.antifraud_timeout_check) {
            GlobalSettings.callProcedure({ procedure: 'public.set_antifraud_timeout_accept' })
                .then(function(response) {});
        } else {
            GlobalSettings.callProcedure({ procedure: 'public.set_antifraud_timeout_manual' })
                .then(function(response) {});
        }
    };

    $scope.save = function() {
        GlobalSettings.update($scope.settings).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.cancel = function() {
        $modalInstance.dismiss();
    };
};
