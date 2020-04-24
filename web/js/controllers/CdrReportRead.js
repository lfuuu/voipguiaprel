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

        $scope.item.time_from = dateFrom;
        $scope.item.time_to = dateTo;

        $scope.clickSearch();
    };

    $scope.initWithParams = function () {
        var date = new Date();
        date.setHours(date.getHours() + 3);

        var dateTo = date.toISOString().slice(0, 19).replace('T', ' ');
        date.setDate(date.getDate() - 1);
        var dateFrom = date.toISOString().slice(0, 19).replace('T', ' ');
        
        if (params.object_type == 'testDial') {
            TestDial.getForCdr(params.object_id).then(function (testData) {
                
                List.hub().then(function (data) {
                    $scope.hubList = data;

                    Trunk.listNameAndAlias(testData.hub_id).then(function (dataTrunk) {
                        $scope.trunkNameList = dataTrunk;

                        $scope.item.src_number = testData.src_number;
                        $scope.item.dst_number = testData.dst_number;
                        $scope.item.redirect_number = testData.redirect_number;
                        $scope.item.src_route = testData.src_route;
                        $scope.item.dst_route = testData.dst_route;
                        $scope.item.time_from = dateFrom;
                        $scope.item.time_to = dateTo;
                        $scope.item.hub_id = testData.hub_id;
                        $scope.item.show_all = true;
                        $scope.item.sort_asc = false;
    
                        $scope.clickSearch();
                    });
                });
                
            });
        }
    };
    
    $scope.init = function(tab) {
        if (tab) tab.title = 'Отчет по CDR';
        
        $scope.list = [];

        $scope.item = {
            src_number: '',
            dst_number: '',
            redirect_number: '',
            src_route: '',
            dst_route: '',
            time_relative: '',
            limit: 100,
            hub_id: '',
            mcn_callid: '',
            sort_asc: true,
            show_all: true,
            is_time_absolute: true,
            disconnect_cause_id: '',
            out_redirect_number: '',
            source: 'all',
            session_compare: '',
            session_time: ''
        };

        if (params.object_type && params.object_id) {
            $scope.initWithParams();
        } else {
            $scope.initDefault();
        }

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

    if (!(params.object_type && params.object_id)) {
        List.hub().then(function (data) {
            $scope.hubList = data;
        });
    }

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