var MajorTestCtrl = function($scope, Major, params, $modalInstance, $window) {

    if (params.id) {
        Major.test({id: params.id, factor: params.factor}).then(function(data){
            $scope.item = data.item;
            $scope.url = data.url;
        });
    }

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};