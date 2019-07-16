var OcaBwEditCtrl = function ($scope, OcaBw, params, $modalInstance, $window) {

  $scope.addEditable = false;
  $scope.deleteEditable = false;
  $scope.readEditable = false;

  $scope.serverList = [
    {id: 99, name: 'Москва'},
    {id: 98, name: 'Санкт-Петербург'},
    {id: 93, name: 'Казань'},
    {id: 95, name: 'Екатеринбург'},
    {id: 94, name: 'Новосибирск'},
    {id: 89, name: 'Владивосток'},
    {id: 97, name: 'Краснодар'},
    {id: 11, name: 'MSK_HUB'},
    {id: 81, name: 'Будапешт'},
    {id: 61, name: 'Австрия'},
    {id: 20, name: 'EU_HUB'}
  ];

  if (params.id) {
    OcaBw.get({id: params.id}).then(function (data) {
      $scope.item = data;
    });
  } else {
    $scope.item = {
      prefixlist: []
    };
  }

  $scope.addPrefixlist = function () {
    $scope.item.prefixlist.push({id: null});
  };

  $scope.removePrefixlist = function (index) {
    $scope.item.prefixlist.splice(index, 1);
  };

  $scope.save = function () {
    OcaBw.save($scope.item).then(function (response) {
      $modalInstance.close();
    });
  };

  $scope.back = function () {
    $modalInstance.dismiss();
  }
};