var SmsCdrReportReadCtrl = function ($rootScope, $scope, SmsCdr, List, $window, $modal) {
    var params = $rootScope.tabs[0].params || {};
  
    $scope.sortType    = 'dt_create';
    $scope.sortReverse = false;
    $scope.hideFilter  = false;
    $scope.isLoading   = false;
    $scope.noData      = false;
  
    $scope.item = {
      sessionid: '',
      server_id: '',
      limit: 100,
      msisdn: '',
      destination: '',
      direction: '',
      mcc: '',
      mnc: '',
      is_time_absolute: true,
      time_from: '',
      time_to: '',
      time_relative: '',
      sort_asc: true
    };
  
    $scope.timeIntervals    = List.timeInterval();
    $scope.countryOptions   = [];
    $scope.networkOptions   = [];
  
    $scope.clickSearch = function() {
      $scope.isLoading = true;
      $scope.noData    = false;
  
      SmsCdr.read($scope.item).then(function(data) {
        $scope.list   = data;
        $scope.noData = (data.length === 0);
  
        var cMap = {}, nMap = {};
        data.forEach(function(r) {
          if (r.mcc != null)    cMap[r.mcc]     = r.country;
          if (r.mnc != null)    nMap[r.mnc]     = r.network;
        });
  
        $scope.countryOptions = Object.keys(cMap).sort().map(function(code) {
          return { mcc: code, country: cMap[code] };
        });
        $scope.networkOptions = Object.keys(nMap).sort().map(function(code) {
          return { mnc: code, network: nMap[code] };
        });
  
        $scope.isLoading = false;
      }, function(err) {
        $scope.isLoading = false;
        $window.alert('Ошибка запроса: ' + err);
      });
    };
  
    $scope.initDefault = function() {
      var to   = new Date(),
          from = new Date(to.getTime() - 60 * 1000);
      $scope.item.time_from = from.toISOString().slice(0,19).replace('T',' ');
      $scope.item.time_to   = to  .toISOString().slice(0,19).replace('T',' ');
      $scope.clickSearch();
    };
  
    $scope.init = function(tab) {
      if (tab) tab.title = 'Отчёт по SMS CDR';
      $scope.initDefault();
    };
  
    $scope.sort = function(field) {
      if ($scope.sortType === field) {
        $scope.sortReverse = !$scope.sortReverse;
      } else {
        $scope.sortType    = field;
        $scope.sortReverse = false;
      }
    };
  
    $scope.openSmsRawModal = function(item) {
      $modal.open({
        templateUrl: '/templates/sms/sms_raw_view.html',
        controller:  SmsRawViewCtrl,
        resolve:     { params: function(){ return { cdr_id: item.id }; } }
      });
    };
  
    $scope.init($rootScope.tabs[$rootScope.tabs.length - 1]);
  };
  