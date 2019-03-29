var CdrReportViewCtrl = function ($scope, Redirect, Cdr, params, $modalInstance, $window) {

    if (params.mcn_callid) {
        $scope.mcn_callid = params.mcn_callid;
        Cdr.get({mcn_callid: params.mcn_callid}).then(function (data) {
            $scope.list = data;
        });
    } else {
        $scope.mcn_callid = '';
        $scope.list = [];
    }

    $scope.clickSubItem = function (subitem) {
        Redirect.callsRawView(subitem).then(function () {
            $scope.init();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    }
};