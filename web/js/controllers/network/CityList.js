var RussianCityListCtrl = function($scope, City) {
    $scope.cities = [];

    $scope.init = function() {
        City.read().then(function(data) {
            $scope.cities = data;
        });
    };

    $scope.init();
};

