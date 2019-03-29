var TrunkEditCtrl = function($rootScope, $scope, Redirect, Trunk, params, $modalInstance, STAT_HOST, $window) {
    var serverId;

    if (params.server_id) {
        serverId = params.server_id;
    } else {
        serverId =  $rootScope.server.id;
    }

    if (params.id) {
        Trunk.get({id: params.id, region_id: serverId}).then(function (data) {
            $scope.item = data;

            Trunk.serviceTrunks(params.id).then(function (serviceTrunks) {
                $scope.serviceTrunks = serviceTrunks;
            });

            Trunk.findUsagesInTrunkGroups(params.id).then(function (result) {
                $scope.usagesInTrunkGroups = result;
            });

            var numbersRules = {};
            $.each(data.numbersRules, function () {
                var mode = (this.orig ? 'orig' : 'term') + '-' + (!this.outgoing ? 'a' : 'b');

                if (!numbersRules[mode]) {
                    numbersRules[mode] = [];
                }

                numbersRules[mode].push({
                    allow: this.allow,
                    orig: this.orig,
                    outgoing: this.outgoing,
                    prefixlist_id: this.prefixlist_id,
                    test_redirect_num: this.test_redirect_num,
                });
            });

            $scope.item.numbersRules = numbersRules;

            if (data.trunkSorm.length > 0) {
                $scope.item.sorm = {
                    enabled: true,
                    name: data.trunkSorm[0].name,
                    ip_addr: data.trunkSorm[0].ip_addr,
                    groups: data.trunkSorm[0].groups.replace('{', '').replace('}', '').split(','),
                    sorm_operator_id: data.trunkSorm[0].sorm_operator_id,
                    source_type_id: data.trunkSorm[0].source_type_id,
                    items: []
                };

                for(var id in data.trunkSorm) {
                    $scope.item.sorm.items.push({
                        id: data.trunkSorm[id].id,
                        old_name: data.trunkSorm[id].old_name,
                        is_show: data.trunkSorm[id].is_show
                    });
                }
            } else {
                $scope.item.sorm = {
                    enabled: false,
                    source_type_id: '',
                    name: '',
                    ip_addr: '',
                    groups: {},
                    items: []
                };
            }

            if (data.usagesInMarketplace.length == 0 ||
                (data.usagesInMarketplace[0]['uplink_enabled'] == false &&
                (data.usagesInMarketplace[0]['trunk_groups'] == '{}' || data.usagesInMarketplace[0]['trunk_groups'] == null))
            ) {
                $scope.item.used_in_marketplace = false;
            } else {
                $scope.item.used_in_marketplace = true;
            }
        });
    } else {
        $scope.item = {
            server_id: serverId,
            default_priority: 0,
            source_rule_default_allowed: false,
            source_trunk_rule_default_allowed: false,
            destination_rule_default_allowed: false,
            priorities: [],
            trunkRules: [],
            numberPreprocessing: [],
            loadLimit: [],
            numbersRules: {},
            orig_afilter_default_allowed: true,
            orig_bfilter_default_allowed: true,
            term_afilter_default_allowed: true,
            term_bfilter_default_allowed: true,
            location_id: 1,
            sorm: {
                enabled: false,
                source_type_id: '',
                name: '',
                ip_addr: '',
                is_show: false,
                groups: {},
                sorm_operator_id: 1
            }
        };
    }

    $scope.addPriority = function () {
        $scope.item.priorities.push({prefixlist_id: '', priority: 0});
    };

    $scope.removePriority = function (index) {
        $scope.item.priorities.splice(index, 1);
    };

    $scope.transcriptMode = function (mode) {
        switch (mode) {
            case 'orig-a':
                return {
                    key: mode,
                    init: 'orig_afilter_default_allowed',
                    orig: true,
                    outgoing: false,
                    is_test_redirect_num: true
                };
            case 'orig-b':
                return {
                    key: mode,
                    init: 'orig_bfilter_default_allowed',
                    orig: true,
                    outgoing: true
                };
            case 'term-a':
                return {
                    key: mode,
                    init: 'term_afilter_default_allowed',
                    orig: false,
                    outgoing: false,
                    is_test_redirect_num: true
                };
            case 'term-b':
                return {
                    key: mode,
                    init: 'term_bfilter_default_allowed',
                    orig: false,
                    outgoing: true
                };
        }
    };

    $scope.locationIds = [
        {id: 1, name: 'Домашний регион'},
        {id: 2, name: 'Национальный роуминг'},
        {id: 3, name: 'Международный роуминг'}
    ];

    $scope.groupTypes = [
        {id: 1, name: 'Транзит'},
        {id: 2, name: 'Местный узел'},
        {id: 3, name: 'Точка подключения'},
        {id: 4, name: 'Специальная'}
    ];

    $scope.addNumbersRule = function (key) {
        var mode = $scope.transcriptMode(key);

        if (!$scope.item.numbersRules[key]) {
            $scope.item.numbersRules[key] = [];
        }

        $scope.item.numbersRules[key].push({
            prefixlist_id: '',
            allow: $scope.item[mode.init],
            orig: mode.orig,
            outgoing: mode.outgoing,
            test_redirect_num: mode.test_redirect_num
        });
    };

    $scope.removeNumbersRule = function (key, index) {
        $scope.item.numbersRules[key].splice(index, 1);
    };

    $scope.addTrunkRule = function () {
        $scope.item.trunkRules.push({
            trunk_group_id: '',
            allow: $scope.item.source_trunk_rule_default_allowed
        });
    };

    $scope.removeTrunkRule = function (index) {
        $scope.item.trunkRules.splice(index, 1);
    };

    $scope.getNumbersRuleMode = function (mode) {
        return mode ? 'orig' : 'term';
    };

    $scope.setNumbersRuleMode = function () {
        $scope.numbersRulesOrig = !$scope.numbersRulesOrig;
    };

    $scope.addNumberPreprocessing = function (src) {
        $scope.item.numberPreprocessing.push({src: src, noa: '', length: '', prefix: ''});
    };

    $scope.removeNumberPreprocessing = function (index) {
        $scope.item.numberPreprocessing.splice(index, 1);
    };

    $scope.validateIPAddress= function (ip) {
        if (!/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(ip))
        {
            alert("Некорректный IP-адрес");
            return false;
        } else {
            return true;
        }
    };

    $scope.save = function () {
        if ($scope.item.do_sync && $scope.item.auto_routing != $scope.item.default_auto_routing) {
            if (!$window.confirm('Произойдет синхронизация прайс-листов. Вы уверены?')) return;
        }

        if ($scope.item.sorm.enabled && $scope.item.sorm.ip_addr != '' && !$scope.validateIPAddress($scope.item.sorm.ip_addr)) {
            return;
        }

        $scope.item.region_id = serverId;

        Trunk.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };

    $scope.openServiceTrunks = function () {
        $.each($scope.serviceTrunks, function () {
            $window.open(
                STAT_HOST + '/usage/trunk/edit-by?' + $.param({
                    'clientAccountId': this.client_account_id,
                    'trunkId': this.trunk_id
                })
            );
        });
    };

    $scope.toggleSormIsShow = function () {
        if ($scope.item.sorm.enabled) {
            $scope.item.sorm.is_show = !$scope.item.sorm.is_show;
        }
    };

    $scope.removeSormItem = function (index) {
        if ($scope.item.name == $scope.item.sorm.items[index].old_name) {
            return;
        }

        $scope.item.sorm.items.splice(index, 1);
    };

    $scope.addSormItem = function () {
        if ($scope.item.sorm.enabled) {
            var oldName = '';

            if ($scope.item.sorm.items.length == 0) {
                oldName = $scope.item.name;
            }

            $scope.item.sorm.items.push({
                old_name: oldName,
                is_show: false
            });
        }
    };

    $scope.addLoadLimit = function () {
        $scope.item.loadLimit.push({
            trunk_id: $scope.item.id,
            is_orig: true
        });
    };

    $scope.removeLoadLimit = function (index) {
        $scope.item.loadLimit.splice(index, 1);
    };

    $scope.clickTrunkGroupItem = function(item) {
        if (window.getSelection().type == 'Range') return;

        Redirect.trunkGroupEdit(item.id).then(function () {
            $scope.init();
        });
    };

    $scope.hasPopover = function () {
        return $scope.item.used_in_marketplace ? 'mouseenter' : 'none';
    };
};