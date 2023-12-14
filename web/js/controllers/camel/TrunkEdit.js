var CamelTrunkEditCtrl = function($rootScope, $scope, Redirect, CamelTrunk, CamelList, params, $modalInstance, List) {
    $scope.CAMEL_MTS_ANTIFRAUD = 1;
    $scope.CAMEL_EPVV_ANTIFRAUD = 2;
    $scope.camelNnpMode = $scope.CAMEL_EPVV_ANTIFRAUD;
    
    if (params.id) {
        CamelTrunk.get({id: params.id}).then(function (data) {
            $scope.item = data;

            $scope.item.camelTrunkRulesAntifraudEpvvOrig = [];
            $scope.item.camelTrunkRulesAntifraudEpvvTerm = [];
            $.each(data.camelTrunkRulesAntifraud, function () {
                if (this.is_orig) {
                    $scope.item.camelTrunkRulesAntifraudEpvvOrig.push(this);
                } else {
                    $scope.item.camelTrunkRulesAntifraudEpvvTerm.push(this);
                }
            })
        });

    } else if (params.copy_id) {
       CamelTrunk.copy({id: params.copy_id}).then(function (data) {
            $scope.item = {
                name: '',
                server_id: $scope.server.id,
                numberPreprocessing: data,
                camelGtRules: [],
                is_route_incoming_calls: false,
                gt_rule_default_allowed: false,
                camelTrunkRulesAntifraud: [],
                camelTrunkRulesAntifraudEpvvOrig: [],
                camelTrunkRulesAntifraudEpvvTerm: [],
                mts_orig: false,
                epvv_orig: false,
                mts_term: false,
                epvv_term: false,
            }
       });
    } else {
        $scope.item = {
            name: '',
            server_id: $scope.server.id,
            numberPreprocessing: [],
            camelGtRules: [],
            is_route_incoming_calls: false,
            gt_rule_default_allowed: false,
        };
    }
    
    $scope.locationIds = [
        {id: 1, name: 'Домашний регион'},
        {id: 2, name: 'Гостевой регион'},
        {id: 3, name: 'Международный роуминг'}
    ];

    $scope.trunkNumberPreprocessingType = [
        {'id': 0, 'name': 'Префикс'},
        {'id': 1, 'name': 'Вставка'},
        {'id': 2, 'name': 'Добавление в конец'},
        {'id': 3, 'name': 'Удаление'},
        {'id': 4, 'name': 'Замена'},
        {'id': 5, 'name': 'Замена если номер'}
    ];

    CamelList.routeTable({server_id: $scope.server.id}).then(function (data) {
        $scope.routeTableList = data;
    });

    List.uvrGroup().then(function (data) {
        $scope.uvrGroup = data;
    });

    $scope.save = function () {
        $scope.item.camelTrunkRulesAntifraud = [];
        $.each($scope.item.camelTrunkRulesAntifraudEpvvOrig, function () {
            $scope.item.camelTrunkRulesAntifraud.push(this);
        });

        $.each($scope.item.camelTrunkRulesAntifraudEpvvTerm, function () {
            $scope.item.camelTrunkRulesAntifraud.push(this);
        });
        CamelTrunk.save($scope.item).then(function () {
            $modalInstance.close();
        });
    };

    $scope.addCamelTrunkAntifraudRule = function (defaultMode, type, is_orig) {
        antifraudDefaultModes = {
            "mts_orig": $scope.item.mts_orig,
            "mts_term": $scope.item.mts_term,
            "epvv_orig": $scope.item.epvv_orig,
            "epvv_term": $scope.item.epvv_term
        }

        if (is_orig && type == 'epvv') {
            $scope.item.camelTrunkRulesAntifraudEpvvOrig.push({
                uvr_group_id: $scope.uvrGroup[1].id,
                allow: antifraudDefaultModes[defaultMode],
                antifrod_system_type: type,
                is_orig: is_orig
            })
        } else {
            $scope.item.camelTrunkRulesAntifraudEpvvTerm.push({
                uvr_group_id: $scope.uvrGroup[1].id,
                allow: antifraudDefaultModes[defaultMode],
                antifrod_system_type: type,
                is_orig: is_orig
            })
        }
        // $scope.item.camelTrunkRulesAntifraud.push({
        //     uvr_group_id: $scope.uvrGroup[1].id,
        //     allow: antifraudDefaultModes[defaultMode],
        //     antifrod_system_type: type,
        //     is_orig: is_orig
        // });
    };

    $scope.removeCamelTrunkAntifraudRuleEpvvTerm = function (index) {
        $scope.item.camelTrunkRulesAntifraudEpvvTerm.splice(index, 1);
    };
    $scope.removeCamelTrunkAntifraudRuleEpvvOrig = function (index) {
        $scope.item.camelTrunkRulesAntifraudEpvvOrig.splice(index, 1);
    };

    // $scope.removeCamelTrunkAntifraudRule = function (index) {
    //     $scope.item.camelTrunkRulesAntifraud.splice(index, 1);
    // };

    $scope.setCamelAntifraudMode = function(nnpMode) {
        $scope.camelNnpMode = nnpMode;
    };

    $scope.addNumberPreprocessing = function (abc_mode) {
        $scope.item.numberPreprocessing.push({src: false, abc_mode: abc_mode, noa: '', length: '', prefix: '',
            avoid_mod: false, mod_type: 0, start_pos: 1, end_pos: 1, mod_value: '',
            regex: '', acc: true, auth: true});
    };

    $scope.preprocPrefix = function (item) {
        return !item.mod_type || item.mod_type == 0;
    };

    $scope.preprocPosRange = function(line) {
        return line.mod_type && line.mod_type != 2 && line.mod_type != 0 && line.mod_type != 1;
    };

    $scope.preprocValue = function(line) {
        return line.mod_type && line.mod_type != 0 && line.mod_type != 3;
    };

    $scope.removeNumberPreprocessing = function (index) {
        $scope.item.numberPreprocessing.splice(index, 1);
    };

    $scope.addCamelGtRule = function () {
        $scope.item.camelGtRules.push({
            prefixlist_id: '',
            allow: $scope.item.gt_rule_default_allowed
        });
    };

    $scope.removeCamelGtRule = function (index) {
        $scope.item.camelGtRules.splice(index, 1);
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
