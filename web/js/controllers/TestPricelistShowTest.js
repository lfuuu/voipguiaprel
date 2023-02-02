var TestPricelistShowTestCtrl = function($scope, TestPricelist, Redirect, params, $modalInstance) {
    
    if (params.id) {
        TestPricelist.result({id: params.id}).then(function (data) {
            $scope.item = data;
            $scope.url = data.url;
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