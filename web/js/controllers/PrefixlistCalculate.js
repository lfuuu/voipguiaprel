var PrefixlistCalculateCtrl = function($scope, $rootScope, params, $modalInstance, Prefixlist) {

    var STATUS_SUCCESS = 'SUCCESS';
    var STATUS_ERROR = 'ERROR';

    $scope.processFailed = false;
    $scope.processComplete = false;
    $scope.processed = false;

    if (params.prefixlist) {
        $scope.processed = true;
        $scope.prefixlist = params.prefixlist;

        Prefixlist.nnpCalculation($scope.prefixlist.id).then(function (data) {
            $scope.processed = false;

            if (data.response == STATUS_ERROR) {
                $scope.processFailed = data.message;
                return false;
            }

            if (data.message.status != STATUS_SUCCESS) {
                $scope.processFailed = data.message.message;
                return false;
            }

            $scope.processComplete = true;
            $scope.processCompleteCount = data.message.prefix_list_size;
            $rootScope.$broadcast('prefixlistUpdateCount', $scope.processCompleteCount);
        });
    }

    $scope.back = function () {
        $rootScope.$broadcast('prefixlistUpdateSuccess', $scope.processCompleteCount);
        $modalInstance.dismiss();
    };

};