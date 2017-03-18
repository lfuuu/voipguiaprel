var PrefixlistCalculateCtrl = function($scope, $rootScope, params, $modalInstance) {

    $scope.processFailed = false;
    $scope.canProcess = false;
    $scope.processErrors = [];

    if (params.prefixlist) {
        $scope.canProcess = true;
        $scope.prefixlist = params.prefixlist;

        var requestData = [];

        $.ajax({
            url: 'http://10.252.0.122:8032/test/nnpcalc?cmd=fillNNPPrefixList&id=' + $scope.prefixlist.id,
            dataType: 'json',
            data: requestData,
            success: function (response) {
                console.log(response);
                if (response.status == 'FAILED') {
                    response.each(function (key) {
                        console.log(key);
                        console.log(this);
                    });
                    //$scope.processErrors.push()
                }
            },
            error: function () {
                //$scope.processFailed =
            }
        });
    }

    $scope.back = function () {
        $modalInstance.dismiss();
    };

};