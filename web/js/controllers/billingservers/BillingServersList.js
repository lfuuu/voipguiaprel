// Обязательно объявляем в глобальной области:
var BillingServersListCtrl = function($scope, BillingServer, Redirect, $window) {
  // Параметры сортировки/фильтра
  $scope.sortType     = 'name';
  $scope.sortReverse  = false;
  $scope.filterObj    = {};
  $scope.filterFields = ['id','name','ip','contact_info'];

  // Кастомный фильтр
  $scope.customFilter = function(item) {
    if (!$scope.filterObj) return true;
    for (var key in $scope.filterObj) {
      if ($scope.filterObj[key]) {
        var iv = (item[key]||'').toString().toLowerCase(),
            fv = $scope.filterObj[key].toString().toLowerCase();
        if (iv.indexOf(fv) === -1) return false;
      }
    }
    return true;
  };

  // Загрузка списка
  $scope.init = function(tab) {
    if (tab) tab.title = 'Список биллинговых серверов';
    BillingServer.read().then(function(data) {
      data.sort(function(a,b){
        return (''+a.name).localeCompare(''+b.name);
      });
      $scope.list = data;
    });
  };
  $scope.init();

  // Создание
  $scope.clickCreate = function() {
    Redirect.billingServerCreate().then(function(){ $scope.init(); });
  };

  // Редактирование
  $scope.clickItem = function(item) {
    if (window.getSelection().type === 'Range') return;
    Redirect.billingServerEdit(item.id).then(function(){ $scope.init(); });
  };

  // Удаление
  $scope.deleteItem = function(item) {
    if (!userPermissions['billing_servers_edit']) return;
    if (!$window.confirm('Удалить?')) return;
    BillingServer.delete(item.id).then(function(){ $scope.init(); });
  };
};

// Регистрируем контроллер в Angular
app.controller(
  'BillingServersListCtrl',
  ['$scope','BillingServer','Redirect','$window', BillingServersListCtrl]
);
