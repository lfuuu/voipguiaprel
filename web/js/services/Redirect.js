app.factory('Redirect', function ($window, $rootScope, $modal, $cookies) {

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

    var openTab = function(controller, template, params, selectedTab) {
        $rootScope.selectedTab = (typeof selectedTab == 'undefined') ? 'routing' : selectedTab;
        var key = controller + template + params + $rootScope.selectedTab;
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
            $cookies.selectedPage = 'attributeList';
            return openTab(AttributeListCtrl, '/templates/attribute_list.html');
        },
        attributeEdit: function (id) {
            return openModal(AttributeEditCtrl, '/templates/attribute_edit.html', {id: id});
        },
        attributeCreate: function () {
            return openModal(AttributeEditCtrl, '/templates/attribute_edit.html', {id: null});
        },
        attributeGroupList: function () {
            $cookies.selectedPage = 'attributeGroupList';
            return openTab(AttributeGroupListCtrl, '/templates/attribute_group_list.html');
        },
        attributeGroupEdit: function (id) {
            return openModal(AttributeGroupEditCtrl, '/templates/attribute_group_edit.html', {id: id});
        },
        attributeGroupCreate: function (id) {
            return openModal(AttributeGroupEditCtrl, '/templates/attribute_group_edit.html', {id: null});
        },

        trunkList: function() {
            $cookies.selectedPage = 'trunkList';
            return openTab(TrunkListCtrl, '/templates/trunk_list.html');
        },
        trunkEdit: function(id) {
            return openModal(TrunkEditCtrl, '/templates/trunk_edit.html', {id: id});
        },
        trunkCreate: function() {
            return openModal(TrunkEditCtrl, '/templates/trunk_edit.html', {id: null});
        },
        trunkGroupList: function() {
            $cookies.selectedPage = 'trunkGroupList';
            return openTab(TrunkGroupListCtrl, '/templates/trunk_group_list.html');
        },
        trunkGroupEdit: function(id) {
            return openModal(TrunkGroupEditCtrl, '/templates/trunk_group_edit.html', {id: id});
        },
        trunkGroupCreate: function() {
            return openModal(TrunkGroupEditCtrl, '/templates/trunk_group_edit.html', {id: null});
        },

        prefixlistList: function() {
            $cookies.selectedPage = 'prefixlistList';
            return openTab(PrefixlistListCtrl, '/templates/prefixlist_list.html');
        },
        prefixlistEdit: function(id) {
            return openModal(PrefixlistEditCtrl, '/templates/prefixlist_edit.html', {id: id});
        },
        prefixlistCreate: function() {
            return openModal(PrefixlistEditCtrl, '/templates/prefixlist_edit.html', {id: null});
        },

        routeCaseList: function() {
            $cookies.selectedPage = 'routeCaseList';
            return openTab(RouteCaseListCtrl, '/templates/route_case_list.html');
        },
        routeCaseEdit: function(id) {
            return openModal(RouteCaseEditCtrl, '/templates/route_case_edit.html', {id: id});
        },
        routeCaseCreate: function() {
            return openModal(RouteCaseEditCtrl, '/templates/route_case_edit.html', {id: null});
        },

        outcomeList: function() {
            $cookies.selectedPage = 'outcomeList';
            return openTab(OutcomeListCtrl, '/templates/outcome_list.html');
        },
        outcomeEdit: function(id) {
            return openModal(OutcomeEditCtrl, '/templates/outcome_edit.html', {id: id});
        },
        outcomeCreate: function() {
            return openModal(OutcomeEditCtrl, '/templates/outcome_edit.html', {id: null});
        },

        numberList: function() {
            $cookies.selectedPage = 'numberList';
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

        blacklistSettings: function() {
            return openModal(BlacklistSettingsEditCtrl, '/templates/blacklist_settings_edit.html');
        },

        airpList: function() {
            $cookies.selectedPage = 'airpList';
            return openTab(AirpListCtrl, '/templates/airp_list.html');
        },
        airpEdit: function(id) {
            return openModal(AirpEditCtrl, '/templates/airp_edit.html', {id: id});
        },
        airpCreate: function() {
            return openModal(AirpEditCtrl, '/templates/airp_edit.html', {id: null});
        },

        cpcList: function() {
            $cookies.selectedPage = 'cpcList';
            return openTab(CpcListCtrl, '/templates/cpc_list.html');
        },
        cpcEdit: function(id) {
            return openModal(CpcEditCtrl, '/templates/cpc_edit.html', {id: id});
        },
        cpcCreate: function() {
            return openModal(CpcEditCtrl, '/templates/cpc_edit.html', {id: null});
        },

        releaseReasonList: function() {
            $cookies.selectedPage = 'releaseReasonList';
            return openTab(ReleaseReasonListCtrl, '/templates/release_reason_list.html');
        },
        releaseReasonEdit: function(id) {
            return openModal(ReleaseReasonEditCtrl, '/templates/release_reason_edit.html', {id: id});
        },
        releaseReasonCreate: function() {
            return openModal(ReleaseReasonEditCtrl, '/templates/release_reason_edit.html', {id: null});
        },

        routeTableList: function() {
            $cookies.selectedPage = 'routeTableList';
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
            $cookies.selectedPage = 'testAuthList';
            return openTab(TestAuthListCtrl, '/templates/test_auth_list.html');
        },
        testAuthEdit: function(id) {
            return openModal(TestAuthEditCtrl, '/templates/test_auth_edit.html', {id: id});
        },
        testAuthClone: function(id) {
            return openModal(TestAuthEditCtrl, '/templates/test_auth_edit.html', {id: id, clone: true});
        },
        testAuthCloneFromCall: function(id) {
            return openModal(TestAuthEditCtrl, '/templates/test_auth_edit.html', {id: id, cloneFromCall: true});
        },
        testAuthCreate: function(testGroupId) {
            return openModal(TestAuthEditCtrl, '/templates/test_auth_edit.html', {testGroupId: testGroupId});
        },
        testAuthCreateAndFill: function(default_params) {
            return openModal(TestAuthEditCtrl, '/templates/test_auth_edit.html', {default_params: default_params});
        },
        testAuthShowTestPrimary: function(id, ttl) {
            return openModal(TestAuthShowTestPrimaryCtrl, '/templates/test_auth_show_test.html', {id: id, ttl: ttl});
        },
        testAuthShowTestReserve: function(id, ttl) {
            return openModal(TestAuthShowTestReserveCtrl, '/templates/test_auth_show_test.html', {id: id, ttl: ttl});
        },
        testAuthShowTestReserve2: function(id, ttl) {
            return openModal(TestAuthShowTestReserve2Ctrl, '/templates/test_auth_show_test.html', {id: id, ttl: ttl});
        },
        testAuthShowTestDev: function(id, ttl) {
            return openModal(TestAuthShowTestDevCtrl, '/templates/test_auth_show_test.html', {id: id, ttl: ttl});
        },

        testCallList: function() {
            $cookies.selectedPage = 'testCallList';
            return openTab(TestCallListCtrl, '/templates/test_call_list.html');
        },
        testCallEdit: function(id) {
            return openModal(TestCallEditCtrl, '/templates/test_call_edit.html', {id: id});
        },
        testCallClone: function(id) {
            return openModal(TestCallEditCtrl, '/templates/test_call_edit.html', {id: id, clone: true});
        },
        testCallCloneFromAuth: function(id) {
            return openModal(TestCallEditCtrl, '/templates/test_call_edit.html', {id: id, cloneFromAuth: true});
        },
        testCallCreate: function(testGroupId) {
            return openModal(TestCallEditCtrl, '/templates/test_call_edit.html', {testGroupId: testGroupId});
        },
        testCallShowTest: function(id, displayTreeView) {
            return openModal(TestCallShowTestCtrl, '/templates/test_call_show_test.html', {id: id, displayTreeView: displayTreeView});
        },
        testCallShowTestReserve: function(id, displayTreeView) {
            return openModal(TestCallShowTestReserveCtrl, '/templates/test_call_show_test.html', {id: id, displayTreeView: displayTreeView});
        },
        testCallShowTestReserve2: function(id, displayTreeView) {
            return openModal(TestCallShowTestReserve2Ctrl, '/templates/test_call_show_test.html', {id: id, displayTreeView: displayTreeView});
        },
        testCallShowTestDev: function(id, displayTreeView) {
            return openModal(TestCallShowTestDevCtrl, '/templates/test_call_show_test.html', {id: id, displayTreeView: displayTreeView});
        },

        testGroupList: function() {
            $cookies.selectedPage = 'testGroupList';
            return openTab(TestGroupListCtrl, '/templates/test_group_list.html');
        },
        testGroupEdit: function(id) {
            return openModal(TestGroupEditCtrl, '/templates/test_group_edit.html', {id: id});
        },
        testGroupCreate: function() {
            return openModal(TestGroupEditCtrl, '/templates/test_group_edit.html', {id: null});
        },
        statisticsTree: function() {
            $cookies.selectedPage = 'statisticsTree';
            return openTab(StatisticsTreeCtrl, '/templates/statistics_tree.html');
        },
        testGenerateLog: function() {
            return openModal(TestGenerateLogCtrl, '/templates/test_generate_log.html');
        },
        uplinkList: function() {
            $cookies.selectedPage = 'uplinkList';
            return openTab(UplinkListCtrl, '/templates/uplink_list.html');
        },
        uplinkEdit: function(id) {
            return openModal(UplinkEditCtrl, '/templates/uplink_edit.html', {id: id});
        },
        uplinkCreate: function(data) {
            return openModal(UplinkEditCtrl, '/templates/uplink_edit.html', data);
        },
        imsiPartnerList: function() {
            $cookies.selectedPage = 'imsiPartnerList';
            return openTab(ImsiPartnerListCtrl, '/templates/imsi_partner_list.html');
        },
        imsiPartnerEdit: function(id) {
            return openModal(ImsiPartnerEditCtrl, '/templates/imsi_partner_edit.html', {id: id});
        },
        imsiPartnerCreate: function() {
            return openModal(ImsiPartnerEditCtrl, '/templates/imsi_partner_edit.html', {id: null});
        },
        ocaBwList: function() {
            $cookies.selectedPage = 'ocaBwList';
            return openTab(OcaBwListCtrl, '/templates/oca_bw_list.html');
        },
        ocaBwEdit: function(id) {
            return openModal(OcaBwEditCtrl, '/templates/oca_bw_edit.html', {id: id});
        },
        ocaBwCreate: function() {
            return openModal(OcaBwEditCtrl, '/templates/oca_bw_edit.html', {id: null});
        },
        routeReplaceEdit: function() {
            return openModal(RouteReplaceEditCtrl, '/templates/route_replace_edit.html');
        },
        pricelistList: function() {
            $cookies.selectedPage = 'pricelistList';
            return openTab(PricelistListCtrl, '/templates/pricelist_list.html', {}, 'billing');
        },
        pricelistView: function(id) {
            return openModal(PricelistViewCtrl, '/templates/pricelist_view.html', {id: id});
        },
        pricelistShortView: function(id, serviceTypeId) {
            var templatePath;
            var controller;
            if (serviceTypeId == 1) {
                //голос
                templatePath = '/templates/pricelist_short_view.html';
                controller = PricelistShortViewCtrl;
            } else if (serviceTypeId == 2) {
                //смс
                templatePath = '/templates/pricelist_short_view.html';
                controller = PricelistShortViewCtrl;
            } else if (serviceTypeId == 3) {
                //дата (трафик)
                templatePath = '/templates/pricelist_short_view_internet.html';
                controller = PricelistShortViewInternetCtrl;
            }
            return openModal(controller, templatePath, {id: id});
        },
        pricelistEdit: function(id) {
            return openModal(PricelistEditCtrl, '/templates/pricelist_edit.html', {id: id});
        },
        pricelistCreate: function(groupId) {
            return openModal(PricelistEditCtrl, '/templates/pricelist_edit.html', {group_id: groupId});
        },
        pricelistLocationEdit: function(id, pricelistIsActive) {
            return openModal(PricelistLocationEditCtrl, '/templates/pricelist_location_edit.html', {id: id, pricelist_is_active: pricelistIsActive});
        },
        pricelistLocationCreate: function(id, pricelistIsActive) {
            return openModal(PricelistLocationEditCtrl, '/templates/pricelist_location_edit.html', {pricelist_id: id, pricelist_is_active: pricelistIsActive});
        },
        pricelistFilterAEdit: function(id, pricelistIsActive) {
            return openModal(PricelistFilterAEditCtrl, '/templates/pricelist_filter_a_edit.html', {id: id, pricelist_is_active: pricelistIsActive});
        },
        pricelistFilterACreate: function(id, pricelistIsActive) {
            return openModal(PricelistFilterAEditCtrl, '/templates/pricelist_filter_a_edit.html', {location_id: id, pricelist_is_active: pricelistIsActive});
        },
        pricelistFilterBEdit: function(id, pricelistIsActive) {
            return openModal(PricelistFilterBEditCtrl, '/templates/pricelist_filter_b_edit.html', {id: id, pricelist_is_active: pricelistIsActive});
        },
        pricelistFilterBCreate: function(id, pricelistIsActive, pricelistDefaultTarificationFreeSeconds, pricelistDefaultTarificationIntervalSeconds, pricelistDefaultTarificationMinPaidSeconds) {
            return openModal(PricelistFilterBEditCtrl, '/templates/pricelist_filter_b_edit.html', {
                filter_a_id: id,
                pricelist_is_active: pricelistIsActive,
                pricelist_default_tarification_free_seconds: pricelistDefaultTarificationFreeSeconds,
                pricelist_default_tarification_interval_seconds: pricelistDefaultTarificationIntervalSeconds,
                pricelist_default_tarification_min_paid_seconds: pricelistDefaultTarificationMinPaidSeconds
            });
        },
        pricelistPrefixPriceEdit: function (id, pricelistIsActive, pricelistId) {
            return openModal(PricelistPrefixPriceEditCtrl, '/templates/pricelist_prefix_price_edit.html', {
                id: id,
                pricelist_is_active: pricelistIsActive,
                pricelist_id: pricelistId
            });
        },
        pricelistPrefixPriceCreate: function (id, dateStart, pricelistIsActive, pricelistId) {
            return openModal(PricelistPrefixPriceEditCtrl, '/templates/pricelist_prefix_price_edit.html', {
                filter_b_id: id,
                pricelist_date_start: dateStart,
                pricelist_is_active: pricelistIsActive,
                pricelist_id: pricelistId
            });
        },
        pricelistGroupList: function() {
            $cookies.selectedPage = 'pricelistGroupList';
            return openTab(PricelistGroupListCtrl, '/templates/pricelist_group_list.html', {}, 'billing');
        },
        pricelistGroupCreate: function() {
            return openModal(PricelistGroupEditCtrl, '/templates/pricelist_group_edit.html', {});
        },
        pricelistGroupEdit: function(id) {
            return openModal(PricelistGroupEditCtrl, '/templates/pricelist_group_edit.html', {id: id});
        },
        mccList: function() {
            $cookies.selectedPage = 'mccList';
            return openTab(MccListCtrl, '/templates/mcc_list.html', {}, 'billing');
        },
        mccCreate: function() {
            return openModal(MccEditCtrl, '/templates/mcc_edit.html', {});
        },
        mccEdit: function(mcc) {
            return openModal(MccEditCtrl, '/templates/mcc_edit.html', {mcc: mcc});
        },
        mncList: function() {
            $cookies.selectedPage = 'mncList';
            return openTab(MncListCtrl, '/templates/mnc_list.html', {}, 'billing');
        },
        mncCreate: function() {
            return openModal(MncEditCtrl, '/templates/mnc_edit.html', {});
        },
        mncEdit: function(mnc) {
            return openModal(MncEditCtrl, '/templates/mnc_edit.html', {mnc: mnc});
        },
        testPricelistList: function() {
            $cookies.selectedPage = 'testPricelistList';
            return openTab(TestPricelistListCtrl, '/templates/test_pricelist_list.html', {}, 'billing');
        },
        testPricelistEdit: function(id) {
            return openModal(TestPricelistEditCtrl, '/templates/test_pricelist_edit.html', {id: id});
        },
        testPricelistCreate: function() {
            return openModal(TestPricelistEditCtrl, '/templates/test_pricelist_edit.html', {});
        },
        testPricelistShowTest: function(id) {
            return openModal(TestPricelistShowTestCtrl, '/templates/test_pricelist_show_test.html', {id: id});
        },
        testPricelistClone: function(id) {
            return openModal(TestPricelistEditCtrl, '/templates/test_pricelist_edit.html', {id: id, clone: true});
        },
        testPricelistGroupList: function() {
            $cookies.selectedPage = 'testPricelistGroupList';
            return openTab(TestPricelistGroupListCtrl, '/templates/test_pricelist_group_list.html', {}, 'billing');
        },
        testPricelistGroupEdit: function(id) {
            return openModal(TestPricelistGroupEditCtrl, '/templates/test_pricelist_group_edit.html', {id: id});
        },
        testPricelistGroupCreate: function() {
            return openModal(TestPricelistGroupEditCtrl, '/templates/test_pricelist_group_edit.html', {id: null});
        },
        testNumberEdit: function() {
            return openModal(TestNumberEditCtrl, '/templates/test_number_edit.html', {});
        },
        headerList: function() {
            $cookies.selectedPage = 'headerList';
            return openTab(HeaderListCtrl, '/templates/header_list.html');
        },
        headerEdit: function(id) {
            return openModal(HeaderEditCtrl, '/templates/header_edit.html', {id: id});
        },
        headerCreate: function() {
            return openModal(HeaderEditCtrl, '/templates/header_edit.html', {id: null});
        },
        majorList: function() {
            $cookies.selectedPage = 'majorList';
            return openTab(MajorListCtrl, '/templates/major_list.html', {}, 'billing');
        },
        majorEdit: function(id) {
            return openModal(MajorEditCtrl, '/templates/major_edit.html', {id: id});
        },
        majorCreate: function(countryCode, count) {
            return openModal(MajorEditCtrl, '/templates/major_edit.html', {id: null, country_code: countryCode, count: count});
        },
        majorGroupList: function() {
            $cookies.selectedPage = 'majorGroupList';
            return openTab(MajorGroupListCtrl, '/templates/major_group_list.html', {}, 'billing');
        },
        majorGroupCreate: function() {
            return openModal(MajorGroupEditCtrl, '/templates/major_group_edit.html', {});
        },
        majorGroupEdit: function(id) {
            return openModal(MajorGroupEditCtrl, '/templates/major_group_edit.html', {id: id});
        },
    };
});