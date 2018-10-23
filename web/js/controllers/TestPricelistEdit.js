var TestPricelistEditCtrl = function($scope, $rootScope, TestPricelist, Mcc, Mnc, Redirect, List, params, $modalInstance, $window) {

    var watchers = {
        mcc: function (newValue, oldValue) {
            if (newValue != oldValue) {
                $scope.item.mnc = null;

                Mnc.listByMcc({mcc: newValue}).then(function (result) {
                    $scope.mncList = result;
                });
            }
        }
    };

    if (params.id) {
        TestPricelist.get({id: params.id}).then(function (data) {
            $scope.item = data;

            $scope.$watch('item.mcc', watchers.mcc);
        });
    } else {
        $scope.item = {
            name: 'testName',
            location_id: 1,
            is_orig: true
        };

        $scope.$watch('item.mcc', watchers.mcc);
    }

    Mcc.list().then(function (result) {
        $scope.mccList = result;
    });

    Mnc.list().then(function (result) {
        $scope.mncList = result;
    });

    $scope.locationList = List.location();

    $scope.save = function () {
        if ($scope.item.location_id != 3) {
            $scope.item.mcc = '';
            $scope.item.mnc = '';
        }

        TestPricelist.save($scope.item).then(function (response) {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};