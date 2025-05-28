var ActionLogViewCtrl = function ($scope, ActionLog, params, $modalInstance, $window) {

  if (params.id && params.type) {
    var payload = {
      id:   params.id,
      type: params.type
    };
    if (params.action) {
      payload.action = params.action;
    }

    ActionLog.get(payload).then(function (data) {
      $scope.list = $scope.processData(data);
    });
  } else {
    $scope.list = [];
  }

  $scope.processData = function(data) {
    var newData = data;
    // Здесь должна быть обработка полей data_before и data_after,
    // чтобы логи отображались красиво.
    return newData;
  };

  $scope.back = function () {
    $modalInstance.dismiss();
  };
};
