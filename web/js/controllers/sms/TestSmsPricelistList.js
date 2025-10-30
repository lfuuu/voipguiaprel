var TestSmsPricelistListCtrl = function($scope, TestSmsPricelist, Scripts, List, Redirect, $window) {
  $scope.sortType = 'name';
  $scope.sortReverse = false;
  $scope.searchQuery = '';

  $scope.currentPage = 1;
  $scope.limit = 15;
  $scope.offset = (($scope.currentPage - 1) * $scope.limit);
  $scope.totalItems = 0;

  $scope.hideFilter = false;
  $scope.filterFields = [
    'id','name','pricelist_name','a_number',
    'b_number','c_number','location_name','mcc','mnc'
  ];

  $scope.searchArray = { id:'', name:'', group_id:'', result:'', server_id:'', pricelist_id:'' };

  $scope.init = function (tab) {
    if (tab) tab.title = 'SMS Test pricelist';
    $scope.refreshList();
  };

  $scope.locationList = List.location();

  $scope.refreshList = function() {
    TestSmsPricelist.read({
      search_array: $scope.searchArray,
      offset: $scope.offset,
      limit: $scope.limit
    }).then(function (data) {
      $scope.list = data.data;
      $scope.totalItems = data.totalCount;
    });
  };

  List.testPricelistGroup().then(function (data) { $scope.testGroupList = data; });
  List.server().then(function (data) { $scope.serverList = data; });
  List.pricelist().then(function (data) { $scope.pricelistList = data; });

  $scope.testResultList = List.testResult();

  $scope.clickSearch = function(){ $scope.refreshList(); };

  $scope.clickCreate = function () {
    Redirect.testSmsPricelistCreate().then(function () {
      $scope.init();
    });
  };

  $scope.clickItem = function (item) {
    if (window.getSelection().type == 'Range') return;

    Redirect.testSmsPricelistEdit(item.id).then(function () {
      $scope.init();
    });
  };

  $scope.cloneTest = function(item) {
    if (window.getSelection().type == 'Range') return;

    Redirect.testSmsPricelistClone(item.id).then(function () {
      $scope.init();
    });
  };

  $scope.showTestPrimary = function (item) {
  Redirect.testSmsPricelistShowTest(item.id).then(function () {
    $scope.init();
  });
};

$scope.showTestDev = function (item) {
  Redirect.testSmsPricelistShowTestDev(item.id).then(function () {
    $scope.init();
  });
};

// если всё же оставляете универсальный метод — подстрахуем:
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
