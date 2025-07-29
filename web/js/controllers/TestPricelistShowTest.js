var TestPricelistShowTestCtrl = function($scope, TestPricelist, Redirect, params, $modalInstance) {
  if (params.id) {
    TestPricelist.result({id: params.id}).then(function (data) {
      // If the API returned the “type 2” shape with a top‑level trace:
      if (data.trace && data.trace.nodes) {
        // wrap it into an array so the tree builder sees `item.steps`
        data.steps = [{
          name: data.trace.name || 'Start NNP Calc calculation',
          steps: data.trace.nodes,
          match: data.match,
          fix_price: data.fix_price,
          rate_price: data.rate_price,
          interconnect_price: data.interconnect_price
        }];
      }
      // otherwise assume it already has data.steps

      $scope.item    = data;
      $scope.url     = data.url;
      $scope.baseUrl = data.baseUrl;
    });
  }

  $scope.collapseAll = function () {
    $scope.$broadcast('angular-ui-tree:collapse-all');
  };

  $scope.back = function () {
    $modalInstance.dismiss();
  };
};

var TestPricelistShowTestDevCtrl = function($scope, TestPricelist, Redirect, params, $modalInstance) {
    $scope.type = 'Dev';

    if (params.id) {
        TestPricelist.result({id: params.id, isDev: true}).then(function (data) {
            $scope.item = data;
            $scope.url = data.url;
        });
    }

    $scope.collapseAll = function () {
        $scope.$broadcast('angular-ui-tree:collapse-all');
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};