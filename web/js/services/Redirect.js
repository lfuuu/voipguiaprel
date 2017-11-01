app.factory('Redirect', function ($window, $rootScope, $modal) {

    var openModal = function(controller, template, params) {
        var modal = $modal.open({
            templateUrl: template + '?rnd=' + Math.random(),
            controller: controller,
            resolve: {
                params: function() { return params; }
            }
        });
        return modal.result;
    };

    var openTab = function(controller, template, params) {
        var key = controller + template + params;
        var tab;
        if ($rootScope.tabsMap[key] == undefined) {
            tab = {
                key: key,
                controller: controller,
                template: template + '?rnd=' + Math.random(),
                params: params
            };
            $rootScope.tabs.push(tab);
            $rootScope.tabsMap[key] = tab;
        } else {
            tab = $rootScope.tabsMap[key];
        }
        tab.active = true;
        $rootScope.tabs.controller = controller;
    };

    return {
        closeTab: function(tab) {
            $rootScope.tabs.splice($rootScope.tabs.indexOf(tab), 1);
            delete $rootScope.tabsMap[tab.key];
        },

        attributeList: function () {
            return openTab(AttributeListCtrl, '/templates/attribute_list.html');
        },
        attributeEdit: function (id) {
            return openModal(AttributeEditCtrl, '/templates/attribute_edit.html', {id: id});
        },
        attributeCreate: function () {
            return openModal(AttributeEditCtrl, '/templates/attribute_edit.html', {id: null});
        },
        attributeGroupList: function () {
            return openTab(AttributeGroupListCtrl, '/templates/attribute_group_list.html');
        },
        attributeGroupEdit: function (id) {
            return openModal(AttributeGroupEditCtrl, '/templates/attribute_group_edit.html', {id: id});
        },
        attributeGroupCreate: function (id) {
            return openModal(AttributeGroupEditCtrl, '/templates/attribute_group_edit.html', {id: null});
        },

        trunkList: function() {
            return openTab(TrunkListCtrl, '/templates/trunk_list.html');
        },
        trunkEdit: function(id) {
            return openModal(TrunkEditCtrl, '/templates/trunk_edit.html', {id: id});
        },
        trunkCreate: function() {
            return openModal(TrunkEditCtrl, '/templates/trunk_edit.html', {id: null});
        },
        trunkGroupList: function() {
            return openTab(TrunkGroupListCtrl, '/templates/trunk_group_list.html');
        },
        trunkGroupEdit: function(id) {
            return openModal(TrunkGroupEditCtrl, '/templates/trunk_group_edit.html', {id: id});
        },
        trunkGroupCreate: function() {
            return openModal(TrunkGroupEditCtrl, '/templates/trunk_group_edit.html', {id: null});
        },

        prefixlistList: function() {
            return openTab(PrefixlistListCtrl, '/templates/prefixlist_list.html');
        },
        prefixlistEdit: function(id) {
            return openModal(PrefixlistEditCtrl, '/templates/prefixlist_edit.html', {id: id});
        },
        prefixlistCreate: function() {
            return openModal(PrefixlistEditCtrl, '/templates/prefixlist_edit.html', {id: null});
        },

        routeCaseList: function() {
            return openTab(RouteCaseListCtrl, '/templates/route_case_list.html');
        },
        routeCaseEdit: function(id) {
            return openModal(RouteCaseEditCtrl, '/templates/route_case_edit.html', {id: id});
        },
        routeCaseCreate: function() {
            return openModal(RouteCaseEditCtrl, '/templates/route_case_edit.html', {id: null});
        },

        outcomeList: function() {
            return openTab(OutcomeListCtrl, '/templates/outcome_list.html');
        },
        outcomeEdit: function(id) {
            return openModal(OutcomeEditCtrl, '/templates/outcome_edit.html', {id: id});
        },
        outcomeCreate: function() {
            return openModal(OutcomeEditCtrl, '/templates/outcome_edit.html', {id: null});
        },

        numberList: function() {
            return openTab(NumberListCtrl, '/templates/number_list.html');
        },
        numberEdit: function(id) {
            return openModal(NumberEditCtrl, '/templates/number_edit.html', {id: id});
        },
        numberCreate: function() {
            return openModal(NumberEditCtrl, '/templates/number_edit.html', {id: null});
        },

        destinationList: function() {
            return openTab(DestinationListCtrl, '/templates/destination_list.html');
        },
        destinationEdit: function(id) {
            return openModal(DestinationEditCtrl, '/templates/destination_edit.html', {id: id});
        },
        destinationCreate: function() {
            return openModal(DestinationEditCtrl, '/templates/destination_edit.html', {id: null});
        },

        settings: function() {
            return openModal(SettingsEditCtrl, '/templates/settings_edit.html');
        },

        instanceSettings: function() {
            return openModal(InstanceSettingsEditCtrl, '/templates/instance_settings_edit.html');
        },

        airpList: function() {
            return openTab(AirpListCtrl, '/templates/airp_list.html');
        },
        airpEdit: function(id) {
            return openModal(AirpEditCtrl, '/templates/airp_edit.html', {id: id});
        },
        airpCreate: function() {
            return openModal(AirpEditCtrl, '/templates/airp_edit.html', {id: null});
        },

        releaseReasonList: function() {
            return openTab(ReleaseReasonListCtrl, '/templates/release_reason_list.html');
        },
        releaseReasonEdit: function(id) {
            return openModal(ReleaseReasonEditCtrl, '/templates/release_reason_edit.html', {id: id});
        },
        releaseReasonCreate: function() {
            return openModal(ReleaseReasonEditCtrl, '/templates/release_reason_edit.html', {id: null});
        },

        routeTableList: function() {
            return openTab(RouteTableListCtrl, '/templates/route_table_list.html');
        },
        routeTableEdit: function(id) {
            return openModal(RouteTableEditCtrl, '/templates/route_table_edit.html', {id: id});
        },
        routeTableCreate: function() {
            return openModal(RouteTableEditCtrl, '/templates/route_table_edit.html', {id: null});
        },
        routingReport: function() {
            return openTab(RoutingReportCtrl, '/templates/routing_report.html');
        },

        selectRossvyazOperator: function() {
            return openModal(SelectRossvyazOperatorCtrl, '/templates/select_rossvyaz_operator.html');
        },

        testAuthList: function() {
            return openTab(TestAuthListCtrl, '/templates/test_auth_list.html');
        },
        testAuthEdit: function(id) {
            return openModal(TestAuthEditCtrl, '/templates/test_auth_edit.html', {id: id});
        },
        testAuthCreate: function(testGroupId) {
            return openModal(TestAuthEditCtrl, '/templates/test_auth_edit.html', {testGroupId: testGroupId});
        },
        testAuthShowTestPrimary: function(id, displayTreeView) {
            return openModal(TestAuthShowTestPrimaryCtrl, '/templates/test_auth_show_test.html', {id: id, displayTreeView: displayTreeView});
        },
        testAuthShowTestReserve: function(id, displayTreeView) {
            return openModal(TestAuthShowTestReserveCtrl, '/templates/test_auth_show_test.html', {id: id, displayTreeView: displayTreeView});
        },

        testCallList: function() {
            return openTab(TestCallListCtrl, '/templates/test_call_list.html');
        },
        testCallEdit: function(id) {
            return openModal(TestCallEditCtrl, '/templates/test_call_edit.html', {id: id});
        },
        testCallCreate: function(testGroupId) {
            return openModal(TestCallEditCtrl, '/templates/test_call_edit.html', {testGroupId: testGroupId});
        },
        testCallShowTest: function(id, displayTreeView) {
            return openModal(TestCallShowTestCtrl, '/templates/test_call_show_test.html', {id: id, displayTreeView: displayTreeView});
        },

        testGroupList: function() {
            return openTab(TestGroupListCtrl, '/templates/test_group_list.html');
        },
        testGroupEdit: function(id) {
            return openModal(TestGroupEditCtrl, '/templates/test_group_edit.html', {id: id});
        },
        testGroupCreate: function() {
            return openModal(TestGroupEditCtrl, '/templates/test_group_edit.html', {id: null});
        },
    };
});