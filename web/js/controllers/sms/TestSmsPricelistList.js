/* global angular */
var TestSmsPricelistListCtrl = function ($scope, TestSmsPricelist, Scripts, List, Redirect, $window) {
  // Сортировка и поиск
  $scope.sortType = 'name';
  $scope.sortReverse = false;
  $scope.searchQuery = '';

  // Пагинация
  $scope.currentPage = 1;
  $scope.limit = 15;
  $scope.offset = (($scope.currentPage - 1) * $scope.limit);
  $scope.totalItems = 0;

  // Фильтр и поля для client-side поиска
  $scope.hideFilter = false;
  $scope.filterFields = [
    'id', 'name', 'pricelist_name', 'a_number',
    'b_number', 'c_number', 'location_name', 'mcc', 'mnc',
    'expected_trunk' // фронт может искать по ожидаемому транку
  ];

  // Поля server-side запроса
  $scope.searchArray = { id:'', name:'', group_id:'', result:'', server_id:'', pricelist_id:'' };

  // init
  $scope.init = function (tab) {
    if (tab) tab.title = 'SMS Test pricelist';
    $scope.refreshList();
  };

  // Справочники
  $scope.locationList = List.location();
  List.testPricelistGroup().then(function (data) { $scope.testGroupList = data; });
  List.server().then(function (data) { $scope.serverList = data; });
  List.pricelist().then(function (data) { $scope.pricelistList = data; });
  $scope.testResultList = List.testResult();

  // Загрузка списка
  $scope.refreshList = function () {
    TestSmsPricelist.read({
      search_array: $scope.searchArray,
      offset: $scope.offset,
      limit: $scope.limit
    }).then(function (data) {
      // Бек уже возвращает expected_trunk, просто используем
      $scope.list = data.data || [];
      $scope.totalItems = data.totalCount || 0;
    });
  };

  // Действия
  $scope.clickSearch = function(){ $scope.refreshList(); };

  $scope.clickCreate = function () {
    Redirect.testSmsPricelistCreate().then(function () { $scope.init(); });
  };

  $scope.clickItem = function (item) {
    if (window.getSelection().type == 'Range') return;
    Redirect.testSmsPricelistEdit(item.id).then(function () { $scope.init(); });
  };

  $scope.cloneTest = function(item) {
    if (window.getSelection().type == 'Range') return;
    Redirect.testSmsPricelistClone(item.id).then(function () { $scope.init(); });
  };

  $scope.showTestPrimary = function (item) {
    Redirect.testSmsPricelistShowTest(item.id).then(function () { $scope.init(); });
  };

  $scope.showTestDev = function (item) {
    Redirect.testSmsPricelistShowTestDev(item.id).then(function () { $scope.init(); });
  };

  // универсальный вызов
  $scope.showTestBasic = function (item, method) {
    var fn = Redirect[method] || Redirect.testSmsPricelistShowTest;
    return fn(item.id).then(function () { $scope.init(); });
  };

  $scope.deleteItem = function (item) {
    if (!$window.confirm('Удалить?')) return;
    TestSmsPricelist.delete(item.id).then(function () { $scope.init(); });
  };

  $scope.setPagingData = function (page) {
    $scope.offset = ((page - 1) * $scope.limit);
    $scope.refreshList();
  };
};

// Регистрируй контроллер как обычно, если используете angular.module(...)
// angular.module('app').controller('TestSmsPricelistListCtrl', TestSmsPricelistListCtrl);
