var GlobalSettingsCtrl = function($rootScope, $scope, GlobalSettings, $modalInstance) {
    GlobalSettings.get().then(function(data) {
        $scope.settings = data;
        $scope.originalSettings = angular.copy(data);
    });

    $scope.save = function() {
        GlobalSettings.update($scope.settings).then(function(response) {
            if ($scope.originalSettings.antifraud_error_check !== $scope.settings.antifraud_error_check) {
                if ($scope.settings.antifraud_error_check) {
                    GlobalSettings.callProcedure({ procedure: 'public.set_antifraud_error_accept' });
                } else {
                    GlobalSettings.callProcedure({ procedure: 'public.set_antifraud_error_manual' });
                }
            }
            if ($scope.originalSettings.antifraud_reject_check !== $scope.settings.antifraud_reject_check) {
                if ($scope.settings.antifraud_reject_check) {
                    GlobalSettings.callProcedure({ procedure: 'public.set_antifraud_reject_accept' });
                } else {
                    GlobalSettings.callProcedure({ procedure: 'public.set_antifraud_reject_manual' });
                }
            }
            if ($scope.originalSettings.antifraud_timeout_check !== $scope.settings.antifraud_timeout_check) {
                if ($scope.settings.antifraud_timeout_check) {
                    GlobalSettings.callProcedure({ procedure: 'public.set_antifraud_timeout_accept' });
                } else {
                    GlobalSettings.callProcedure({ procedure: 'public.set_antifraud_timeout_manual' });
                }
            }
            $modalInstance.close();
        });
    };

    $scope.cancel = function() {
        $modalInstance.dismiss();
    };
};
