app.controller('A2pSmsCdrReportReadCtrl', function($rootScope, $scope, A2pSmsCdr, List, $window) {
    $scope.sortType    = 'dt_create';
    $scope.sortReverse = false;
    $scope.hideFilter  = false;
    $scope.isLoading   = false;
    $scope.noData      = false;
  
    $scope.item = {
      server_id: '', sms_id: '', src_number: '',
      dst_number: '', src_route: '', dst_route: '',
      status: '', limit: 100,
      is_time_absolute: true,
      time_from:'', time_to:'', time_relative:''
    };
  
    $scope.timeIntervals = List.timeInterval();
  
    $scope.clickSearch = function() {
      $scope.isLoading = true;
      $scope.noData    = false;
      A2pSmsCdr.read($scope.item).then(function(data) {
        $scope.list      = data;
        $scope.noData    = !data.length;
        $scope.isLoading = false;
      }, function(err) {
        $scope.isLoading = false;
        $window.alert('Ошибка: '+err);
      });
    };
  
    $scope.initDefault = function() {
      var to = new Date(), from = new Date(to - 60000);
      $scope.item.time_from = from.toISOString().slice(0,19).replace('T',' ');
      $scope.item.time_to   = to.toISOString().slice(0,19).replace('T',' ');
      $scope.clickSearch();
    };
  
    $scope.init = function(tab) {
      if(tab) tab.title = 'A2P SMS CDR';
      $scope.initDefault();
    };
  
    $scope.sort = function(field) {
      if($scope.sortType===field) $scope.sortReverse = !$scope.sortReverse;
      else { $scope.sortType=field; $scope.sortReverse=false; }
    };
  
    $scope.init($rootScope.tabs[$rootScope.tabs.length-1]);
  });
  