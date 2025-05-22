var A2pSmsCdrReportReadCtrl = function ($rootScope, $scope, A2pSmsCdr, List, $window) {
  var params = $rootScope.tabs[0].params || {};

  $scope.sortType    = 'dt_create';
  $scope.sortReverse = false;
  $scope.hideFilter  = false;
  $scope.isLoading   = false;
  $scope.noData      = false;

  $scope.item = {
    server_id      : '',
    sms_id         : '',
    limit          : 100,
    src_number     : '',
    dst_number     : '',
    src_route      : '',
    dst_route      : '',
    status         : '',
    props          : '',
    is_time_absolute: true,
    time_from      : '',
    time_to        : '',
    time_relative  : '',
    sort_asc       : true
  };

  $scope.timeIntervals = List.timeInterval();

  $scope.clickSearch = function () {
    $scope.isLoading = true;
    $scope.noData    = false;

    A2pSmsCdr.read($scope.item).then(function (data) {
      $scope.list      = data;
      $scope.noData    = (data.length === 0);
      $scope.isLoading = false;
    }, function (err) {
      $scope.isLoading = false;
      $window.alert('Ошибка запроса: ' + err);
    });
  };

  $scope.initDefault = function () {
    var to   = new Date(),
        from = new Date(to.getTime() - 60 * 1000);
    $scope.item.time_from = from.toISOString().slice(0,19).replace('T',' ');
    $scope.item.time_to   = to  .toISOString().slice(0,19).replace('T',' ');
    $scope.clickSearch();
  };

  $scope.init = function (tab) {
    if (tab) tab.title = 'A2P SMS CDR';
    $scope.initDefault();
  };

  $scope.sort = function (field) {
    if ($scope.sortType === field) {
      $scope.sortReverse = !$scope.sortReverse;
    } else {
      $scope.sortType    = field;
      $scope.sortReverse = false;
    }
  };

  // Старт
  $scope.init($rootScope.tabs[$rootScope.tabs.length - 1]);
};
