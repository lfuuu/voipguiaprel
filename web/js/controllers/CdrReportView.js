var CdrReportViewCtrl = function ($scope, Cdr, params, $modalInstance, $window) {

    if (params.mcn_callid) {
        Cdr.get({mcn_callid: params.mcn_callid}).then(function (data) {
            $scope.list = data;
        });
    } else {
        $scope.list = [];
    }

    $scope.back = function () {
        $modalInstance.dismiss();
    }
};