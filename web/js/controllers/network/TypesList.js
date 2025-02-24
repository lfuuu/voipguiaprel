var TypesListCtrl = function($scope, Type) {
    $scope.types = [];

    $scope.init = function() {
        Type.read().then(function(data) {
            $scope.types = data;
        });
    };

    $scope.init();
};

app.controller('TypesListCtrl', TypesListCtrl);
