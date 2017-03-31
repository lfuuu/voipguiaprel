var TrunkEditCtrl = function($scope, Trunk, params, $modalInstance, STAT_HOST, $window) {

    if (params.id) {
        Trunk.get({id: params.id}).then(function (data) {
            $scope.item = data;

            Trunk.serviceTrunks(params.id).then(function (serviceTrunks) {
                $scope.serviceTrunks = serviceTrunks;
            });

            var numbersRules = {};
            $.each(data.numbersRules, function () {
                var mode = (this.orig ? 'orig' : 'term') + '-' + (!this.outgoing ? 'a' : 'b');

                if (!numbersRules[mode]) {
                    numbersRules[mode] = [];
                }

                numbersRules[mode].push({
                    prefixlist_id: this.prefixlist_id,
                    allow: this.allow,
                    orig: this.orig,
                    outgoing: this.outgoing
                });
            });

            $scope.item.numbersRules = numbersRules;
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id,
            default_priority: 0,
            source_rule_default_allowed: false,
            destination_rule_default_allowed: false,
            priorities: [],
            rules: [],
            trunkRules: [],
            numberPreprocessing: [],
            numbersRules: {}
        };
    }

    $scope.addPriority = function () {
        $scope.item.priorities.push({prefixlist_id: '', priority: 0});
    };

    $scope.removePriority = function (index) {
        $scope.item.priorities.splice(index, 1);
    };

    $scope.addRule = function (outgoing) {
        $scope.item.rules.push({prefixlist_id: '', outgoing: outgoing});
    };

    $scope.removeRule = function (index) {
        $scope.item.rules.splice(index, 1);
    };

    $scope.transcriptMode = function (mode) {
        switch (mode) {
            case 'orig-a':
                return {
                    key: mode,
                    init: 'orig_afilter_default_allowed',
                    orig: true,
                    outgoing: false
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
                    outgoing: false
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

    $scope.addNumbersRule = function (key) {
        var mode = $scope.transcriptMode(key);

        if (!$scope.item.numbersRules[key]) {
            $scope.item.numbersRules[key] = [];
        }

        $scope.item.numbersRules[key].push({prefixlist_id: '', allow: $scope.item[mode.init], orig: mode.orig, outgoing: mode.outgoing});
    };

    $scope.removeNumbersRule = function (key, index) {
        $scope.item.numbersRules[key].splice(index, 1);
    };

    $scope.addTrunkRule = function () {
        $scope.item.trunkRules.push({trunk_group_id: ''});
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

    $scope.save = function () {
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

};