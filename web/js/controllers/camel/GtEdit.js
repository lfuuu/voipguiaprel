var CamelGtEditCtrl = function($rootScope, $scope, Redirect, CamelGt, Nnp, params, $modalInstance) {
    var countryLoadComplete = false;
    var regionLoadComplete = false;
    var operatorLoadComplete = false;

    var watchers = {
        country_code: function (newValue, oldValue) {
            regionLoadComplete = false;
            operatorLoadComplete = false;
            if (!newValue || newValue.length == 0) {
                $scope.item.region_id = null;
                $scope.item.operator_id = null;
            }

            if (JSON.stringify(newValue) != JSON.stringify(oldValue)) {
                $scope.regionList = null;

                if (countryLoadComplete) {
                    var region = $scope.item.region_id;
                    var operator = $scope.item.operator_id;
                }

                Nnp.regionList({country_code: newValue}).then(function (data) {
                    $scope.regionList = data;

                    if (countryLoadComplete) {
                        $scope.item.region_id = region;
                        regionLoadComplete = true;
                    }
                });

                Nnp.operatorList({country_code: newValue}).then(function (data) {
                    $scope.operatorList = data;

                    if (countryLoadComplete) {
                        $scope.item.operator_id = operator;
                        operatorLoadComplete = true;
                    }
                });
            }
        }
    };

    if (params.id) {
        CamelGt.get({id: params.id}).then(function (data) {
            $scope.$watch('item.country_code', watchers.country_code);
            Nnp.countryList().then(function (dataCountry) {
                $scope.countryList = dataCountry;
                countryLoadComplete = true;
                $scope.item = data;
            });
        });
    } else {
        $scope.item = {
            name: ''
        };
        $scope.$watch('item.country_code', watchers.country_code);

        Nnp.countryList().then(function (data) {
            $scope.countryList = data;
            countryLoadComplete = true;
        });
    }


    $scope.save = function () {
        CamelGt.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
