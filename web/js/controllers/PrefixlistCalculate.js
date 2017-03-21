var PrefixlistCalculateCtrl = function($scope, $rootScope, params, $modalInstance, Prefixlist) {

    var STATUS_SUCCESS = 'SUCCESS';
    var STATUS_ERROR = 'ERROR';

    $scope.processFailed = false;
    $scope.processComplete = false;
    $scope.processed = false;

    if (params.id && params.name) {
        $scope.processed = true;
        $scope.name = params.name;

        Prefixlist.nnpCalculation(params.id).then(function (data) {
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