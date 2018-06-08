var OcaBwListCtrl = function ($scope, OcaBw, Redirect, $window) {

  $scope.sortType = 'name';
  $scope.sortReverse = false;
  $scope.searchQuery = '';

  $scope.filterFields = [
    'name'
  ];

  $scope.init = function (tab) {
    if (tab) tab.title = 'Списки OCA BW';

    OcaBw.read().then(function (data) {
      $scope.list = data;
    });
  };

  $scope.clickCreate = function () {
    Redirect.ocaBwCreate().then(function () {
      $scope.init();
    });
  };

  $scope.clickItem = function (item) {
    if (!userPermissions['oca_bw_edit']) {
      return;
    }

    if (window.getSelection().type == 'Range') return;

    Redirect.ocaBwEdit(item.id).then(function () {
      $scope.init();
    });
  };

  $scope.deleteItem = function (item) {
    if (!$window.confirm('Удалить?')) return;

    OcaBw.delete(item.id).then(function (response) {
      $scope.init()
    });
  };
};