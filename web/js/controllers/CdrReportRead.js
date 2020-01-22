var CdrReportReadCtrl = function($rootScope, $scope, Cdr, TestDial, Trunk, List, Redirect, $window) {
    var params = $rootScope.tabs[0].params;
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

    $scope.initDefault = function () {
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
            disconnect_cause_id: '',
            out_redirect_number: ''
        };

        $scope.clickSearch();
    };

    $scope.initWithParams = function () {
        var date = new Date();
        date.setHours(date.getHours() + 3);

        var dateTo = date.toISOString().slice(0, 19).replace('T', ' ');
        date.setDate(date.getDate() - 1);
        var dateFrom = date.toISOString().slice(0, 19).replace('T', ' ');
        
        if (params.object_type == 'testDial') {
            TestDial.getForCdr(params.object_id).then(function (data) {
                
                $scope.item = {
                    src_number: data.src_number,
                    dst_number: data.dst_number,
                    redirect_number: data.redirect_number,
                    src_route: data.src_route,
                    dst_route: data.dst_route,
                    time_from: dateFrom,
                    time_to: dateTo,
                    time_relative: '',
                    limit: 100,
                    hub_id: data.hub_id,
                    mcn_callid: '',
                    sort_asc: false,
                    show_all: true,
                    is_time_absolute: true,
                    disconnect_cause_id: '',
                    out_redirect_number: ''
                };

                $scope.$watch('item.hub_id', watchers.hub_id);
                
                Trunk.listNameAndAlias($scope.item.hub_id).then(function (dataTrunk) {
                    $scope.trunkNameList = dataTrunk;
    
                    $scope.item.src_route = data.src_route;
                    $scope.item.dst_route = data.dst_route;

                    $scope.clickSearch();
                });
            });
        }
    };
    
    $scope.init = function(tab) {
        if (tab) tab.title = 'Отчет по CDR';
        
        $scope.list = [];


        if (params.object_type && params.object_id) {
            $scope.initWithParams();
        } else {
            $scope.initDefault();
        }
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

        if ($scope.testDialData) {
            $scope.item.hub_id = $scope.testDialData.hub_id;
        }
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