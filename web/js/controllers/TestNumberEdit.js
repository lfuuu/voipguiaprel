var TestNumberEditCtrl = function($scope, TestPricelist, Redirect, params, $modalInstance) {
    $scope.params = {
        number: ''
    };

    $scope.processNumber = function () {
        TestPricelist.numberResult({number: $scope.params.number}).then(function (data) {
            $scope.item = data;
        });
    };

    $scope.displayNumberRange = function () {
        if (!$scope.item || $scope.item.number_range.length == 0) {
            return '';
        }

        var result = '';

        for (var i in $scope.item.number_range) {
            result += (i + ': ' + $scope.item.number_range[i] + ', ');
        }

        return result;
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};