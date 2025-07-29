var SmsOutcomeEditCtrl = function(
  $rootScope,
  $scope,
  Redirect,
  SmsOutcome,
  SmsList,
  params,
  $modalInstance
) {
  // Если редактируем существующий — подгружаем его
  if (params.id) {
    SmsOutcome.get({ id: params.id }).then(function(data) {
      $scope.item = data;
    });
  } else {
    // Новый — сразу захардкодить server_id = 9
    $scope.item = {
      name: '',
      server_id: 9
    };
  }

  // Справочник типов исходов сразу (не промис)
  $scope.outcomeTypeList = SmsList.outcomeType();

  $scope.save = function() {
    SmsOutcome.save($scope.item).then(function() {
      $modalInstance.close();
    });
  };

  $scope.back = function() {
    $modalInstance.dismiss();
  };
};
