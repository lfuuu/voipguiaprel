var GlobalSettingsCtrl = function($scope, GlobalSettings, $modalInstance) {
    GlobalSettings.get().then(function(data) {
      $scope.settings         = data;
      $scope.originalSettings = angular.copy(data);
    });
  
    $scope.setMode = function(type, accept) {
      if (type === 'error') {
        $scope.settings.antifraud_error_check = accept;
      }
      if (type === 'reject') {
        $scope.settings.antifraud_reject_check = accept;
      }
      if (type === 'timeout') {
        $scope.settings.antifraud_timeout_check = accept;
      }
    };
  
    $scope.save = function() {
        const toCall = [
          $scope.settings.antifraud_error_check
            ? 'public.set_antifraud_error_accept'
            : 'public.set_antifraud_error_manual',
          $scope.settings.antifraud_reject_check
            ? 'public.set_antifraud_reject_accept'
            : 'public.set_antifraud_reject_manual',
          $scope.settings.antifraud_timeout_check
            ? 'public.set_antifraud_timeout_accept'
            : 'public.set_antifraud_timeout_manual'
        ];
      
        let seq = Promise.resolve();
        toCall.forEach(proc => {
          seq = seq.then(() => GlobalSettings.callProcedure({ procedure: proc }));
        });
      
        seq.then(() => {
          $modalInstance.close();
        });
      };
      
  
    $scope.close = function() {
      $modalInstance.close();
    };
  };
  