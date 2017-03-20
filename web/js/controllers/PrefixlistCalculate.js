var PrefixlistCalculateCtrl = function($scope, $rootScope, params, $modalInstance, Prefixlist) {

    $scope.processFailed = false;
    $scope.processComplete = false;
    $scope.processed = false;

    if (params.prefixlist) {
        $scope.processed = true;
        $scope.prefixlist = params.prefixlist;



        Prefixlist.nnpCalculation($scope.prefixlist.id).then(function (data) {
            $scope.processed = false;

            if (data.response == 'error') {
                $scope.processFailed = data.message;
                return false;
            }

            if (data.message.status != 'SUCCESS') {
                $scope.processFailed = data.message.message;
                return false;
            }

            $scope.processComplete = data.message.prefix_list_size;
            $rootScope.$broadcast('prefixlistUpdateCount', $scope.processComplete);
        });
    }

    $scope.back = function () {
        $modalInstance.dismiss();
    };

};