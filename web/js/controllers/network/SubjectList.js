var RussianSubjectListCtrl = function($scope, Subject) {
    $scope.subjects = [];

    $scope.init = function() {
        Subject.read().then(function(data) {
            $scope.subjects = data;
        });
    };

    $scope.init();
};

app.controller('RussianSubjectListCtrl', RussianSubjectListCtrl);
