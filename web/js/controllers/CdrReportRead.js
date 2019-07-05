var CdrReportReadCtrl = function($scope, Cdr, Trunk, List, Redirect, $window) {

    $scope.sortType = 'name';
    $scope.sortReverse = false;
    $scope.searchQuery = '';

    $scope.hideFilter = false;
    $scope.isLoading = false;
    $scope.noData = false;

    $scope.filterFields = [
        'name', 'server_id'
    ];

    $scope.timeIntervals = List.timeInterval();

    $scope.trunkNameList = [];

    var watchers = {
        hub_id: function (newValue, oldValue) {
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
            redirect_number: '',
            src_route: '',
            dst_route: '',
            time_from: dateFrom,
            time_to: dateTo,
            time_relative: '',
            limit: 100,
            hub_id: '',
            mcn_callid: '',
            sort_asc: true,
            show_all: true,
            is_time_absolute: true,
            disconnect_cause_id: ''
        };

        $scope.$watch('item.hub_id', watchers.hub_id);
    };

    $scope.clickSearch = function() {
        $scope.isLoading = true;
        $scope.noData = false;
        Cdr.read($scope.item).then(function (data) {
            if (data.length == 0) {
                $scope.noData = true;
            }

            $scope.list = data;

            $scope.isLoading = false;
        });
    };

    List.disconnectCause().then(function (data) {
        $scope.disconnectCauseList = data;
    });

    List.hub().then(function (data) {
        $scope.hubList = data;
    });

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