var StatusListCtrl = function($scope, Status) {
    $scope.statuses = [];

    $scope.init = function() {
        Status.read().then(function(data) {
            $scope.statuses = data;
        });
    };

    $scope.init();
};

app.controller('StatusListCtrl', StatusListCtrl);
