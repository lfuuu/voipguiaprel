var ActionLogItemViewCtrl = function ($scope, ActionLog, params, $modalInstance, $window) {

  if (params.id) {
    ActionLog.getOne({id: params.id}).then(function (data) {
      $scope.list = $scope.processData(data);
    });
  } else {
    $scope.item = {};
  }

  $scope.processData = function(data) {
    var newData = data;

    // Здесь должна быть обработка полей data_before и data_after,
    // чтобы логи отображались красиво.

    return newData;
  },

  $scope.back = function () {
    $modalInstance.dismiss();
  }
};