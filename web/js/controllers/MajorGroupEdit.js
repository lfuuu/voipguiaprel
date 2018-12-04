var MajorGroupEditCtrl = function($scope, List, MajorGroup, params, $modalInstance, $window) {

    if (params.id) {
        MajorGroup.get({id: params.id}).then(function(data){
            $scope.item = data;
        });
    } else {
        $scope.item = {};
    }

    $scope.save = function()
    {
        MajorGroup.save($scope.item).then(function(response) {
            $modalInstance.close();
        });
    };

    $scope.back = function()
    {
        $modalInstance.dismiss();
    };
};