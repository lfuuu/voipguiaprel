var SmsOutcomeEditCtrl = function(
  $rootScope,
  $scope,
  Redirect,
  SmsOutcome,
  SmsList,
  params,
  $modalInstance
) {
  if (params.id) {
    // Редактируем существующий
    SmsOutcome.get({id: params.id}).then(function (data) {
      $scope.item = data;
    });
  } else {
    // Новый – сразу ставим server_id = 9
    $scope.item = {
      name: '',
      server_id: 9
    };
  }

  // Загружаем справочник типов исходов
  SmsList.outcomeType().then(function(list) {
    $scope.outcomeTypeList = list;
  });

  $scope.save = function () {
    SmsOutcome.save($scope.item).then(function () {
      $modalInstance.close();
    });
  };

  $scope.back = function () {
    $modalInstance.dismiss();
  };
};
