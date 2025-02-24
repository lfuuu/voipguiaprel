var RussianDistrictListCtrl = function($scope, District) {
    $scope.districts = [];

    $scope.init = function() {
        District.read().then(function(data) {
            $scope.districts = data;
        });
    };

    $scope.init();
};

app.controller('RussianDistrictListCtrl', RussianDistrictListCtrl);
