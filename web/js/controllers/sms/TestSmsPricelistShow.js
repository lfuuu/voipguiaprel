var TestSmsPricelistShowTestCtrl = function($scope, TestSmsPricelist, Redirect, params, $modalInstance) {
  if (params.id) {
    TestSmsPricelist.result({ id: params.id }).then(function (data) {
      if (data.trace && data.trace.nodes) {
        data.steps = [{
          name: data.trace.name || 'Start NNP Calc calculation',
          steps: data.trace.nodes,
          match: data.match,
          fix_price: data.fix_price,
          rate_price: data.rate_price,
          interconnect_price: data.interconnect_price
        }];
      }
      $scope.item    = data;
      $scope.url     = data.url;
      $scope.baseUrl = data.baseUrl;
    });
  }
  $scope.collapseAll = function () { $scope.$broadcast('angular-ui-tree:collapse-all'); };
  $scope.back = function () { $modalInstance.dismiss(); };
};

var TestSmsPricelistShowTestDevCtrl = function($scope, TestSmsPricelist, Redirect, params, $modalInstance) {
  $scope.type = 'Dev';
  if (params.id) {
    TestSmsPricelist.result({ id: params.id, isDev: true }).then(function (data) {
      $scope.item = data;
      $scope.url  = data.url;
    });
  }
  $scope.collapseAll = function () { $scope.$broadcast('angular-ui-tree:collapse-all'); };
  $scope.back = function () { $modalInstance.dismiss(); };
};
