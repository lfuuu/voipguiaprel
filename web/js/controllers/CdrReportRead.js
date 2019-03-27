var CdrReportReadCtrl = function($scope, Cdr, Trunk, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.filterFields = [
        'name', 'server_id'
    ];

    $scope.trunkNameList = [];

    var watchers = {
        server_id: function (newValue, oldValue) {
            if (newValue != oldValue && newValue !== '') {
                Trunk.listNameAndAlias(newValue).then(function (data) {
                    $scope.trunkNameList = data;
                });
            } else if (newValue == '') {
                $scope.trunkNameList = [];
            }
        }
    };

    $scope.init = function(tab) {
        if (tab) tab.title = 'Отчет по CDR';

        $scope.list = [];

        var date = new Date();

        var dateTo = date.toISOString().slice(0, 19).replace('T', ' ');
        date.setMinutes(date.getMinutes() - 1);
        var dateFrom = date.toISOString().slice(0, 19).replace('T', ' ');

        $scope.item = {
            src_number: '',
            dst_number: '',
            src_route: '',
            dst_route: '',
            time_from: dateFrom,
            time_to: dateTo,
            limit: 100,
            server_id: '',
            mcn_callid: ''
        };

        $scope.$watch('item.server_id', watchers.server_id);
    };

    $scope.clickSearch = function() {
        Cdr.read($scope.item).then(function (data) {
            $scope.list = data;
        });
    };

    $scope.clickItem = function(item) {
        if (!userPermissions['cdr_report_read']) {
            return;
        }

        if (window.getSelection().type == 'Range') return;

        Redirect.cdrReportView(item.mcn_callid).then(function () {
            $scope.init();
        });
    };
};