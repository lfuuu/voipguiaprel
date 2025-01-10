var CdrReportViewCtrl = function ($scope, Redirect, Cdr, params, $modalInstance, $window, $http, $sce) {

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

    $scope.is_eu = params.is_eu || false;

    $scope.clickSubItem = function (subitem, link) {
        Redirect.callsRawView(subitem, link).then(function () {
            $scope.init();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };

    $scope.responses = {};

    $scope.executeRequest = function (paramsKey, paramsValue) {
        var url = 'https://eridanus3-aggr-legs.veles.mcn.ru:8207/pstn.php';
        var params = Object.assign({ mcn_callid: $scope.mcn_callid }, paramsValue);

        $scope.responses[paramsKey] = 'Загрузка...';

        $http.get(url, { params: params })
            .then(function (response) {
                if (paramsValue.is_debug) {
                    $scope.responses[paramsKey] = $sce.trustAsHtml(response.data);
                } else {
                    $scope.responses[paramsKey] = response.data;
                }
            })
            .catch(function (error) {
                $scope.responses[paramsKey] = 'Ошибка: ' + error.statusText;
            });
    };

    $scope.get573 = function () {
        $scope.executeRequest('573', { is_573: 1 });
    };

    $scope.get573Debug = function () {
        $scope.executeRequest('573_debug', { is_573: 1, is_debug: 1 });
    };

    $scope.get86 = function () {
        $scope.executeRequest('86', { is_86: 1 });
    };

    $scope.get86Debug = function () {
        $scope.executeRequest('86_debug', { is_86: 1, is_debug: 1 });
    };
};
