var CdrReportViewCtrl = function ($scope, Redirect, Cdr, params, $modalInstance, $window) {

    if (params.mcn_callid) {
        $scope.mcn_callid = params.mcn_callid;
        Cdr.get({mcn_callid: params.mcn_callid}).then(function (data) {
            console.log(data);
            $scope.list = data.items;
            $scope.link = data.link;
        });
    } else {
        $scope.mcn_callid = '';
        $scope.list = [];
    }

    $scope.clickSubItem = function (subitem, link) {
        Redirect.callsRawView(subitem, link).then(function () {
            $scope.init();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    }
};