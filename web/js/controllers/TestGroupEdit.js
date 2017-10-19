var TestGroupEditCtrl = function($scope, TestGroup, params, $modalInstance, $window) {

    if (params.id) {
        TestGroup.get({id: params.id}).then(function(data){
            $scope.item = data;
        });
    } else {
        $scope.item = {}
    }

    $scope.save = function()
    {
        TestGroup.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    }
};