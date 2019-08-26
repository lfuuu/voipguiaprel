var ActionLogViewCtrl = function ($scope, ActionLog, params, $modalInstance, $window) {

  if (params.id && params.type) {
    ActionLog.get({id: params.id, type: params.type}).then(function (data) {
      $scope.list = data;
    });
  } else {
    $scope.item = {};
  }

  $scope.back = function () {
    $modalInstance.dismiss();
  }
};