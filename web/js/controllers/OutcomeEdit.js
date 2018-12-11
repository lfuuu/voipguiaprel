var OutcomeEditCtrl = function ($scope, Redirect, Outcome, Header, params, $modalInstance, $window) {

    $scope.TYPE_ID_AUTOMATIC = 1;
    $scope.TYPE_ID_ROUTE_CASE = 2;
    $scope.TYPE_ID_RELEASE_REASON = 3;
    $scope.TYPE_ID_AIRP = 4;
    $scope.TYPE_ID_ACCEPT = 5;
    $scope.TYPE_ID_MEG_TO_REG = 6;
    $scope.TYPE_ID_MEG_TO_MEG = 7;
    $scope.TYPE_ID_TRUNK_GROUP = 8;
    $scope.TYPE_ID_AUTOMATIC_2 = 9;

    if (params.id) {
        Outcome.get({id: params.id}).then(function (data) {
            $scope.item = data;
            $scope.item.header = $scope.parseData(data.header);
            $scope.setType($scope.item.type_id);
        });

        Outcome.findUsagesInRouteTables({id: params.id}).then(function (data) {
            $scope.usagesInRouteTables = data;
        });
    } else {
        $scope.item = {
            server_id: $scope.server.id,
            outcome_rule_default_allowed: true,
            outcomeRules: [],
        };
    }

    $scope.setType = function (type_id) {
        $scope.item.type_id = type_id;
    };

    Header.list().then(function (data) {
        $scope.headerList = data;
    });

    $scope.save = function () {
        if ($scope.item.type_id == $scope.TYPE_ID_AUTOMATIC) {
            $scope.item.route_case_id = null;
            $scope.item.release_reason_id = null;
            $scope.item.airp_id = null;
            $scope.item.calling_station_id = null;
            $scope.item.called_station_id = null;
            $scope.item.header = null;
        }
        if ($scope.item.type_id == $scope.TYPE_ID_ROUTE_CASE) {
            $scope.item.release_reason_id = null;
            $scope.item.airp_id = null;
            $scope.item.header = $scope.stringifyData($scope.item.header);
        }
        if ($scope.item.type_id == $scope.TYPE_ID_RELEASE_REASON) {
            $scope.item.route_case_id = null;
            $scope.item.airp_id = null;
            $scope.item.calling_station_id = null;
            $scope.item.called_station_id = null;
            $scope.item.header = null;
        }
        if ($scope.item.type_id == $scope.TYPE_ID_AIRP) {
            $scope.item.route_case_id = null;
            $scope.item.release_reason_id = null;
            $scope.item.header = null;
        }
        if ($scope.item.type_id == $scope.TYPE_ID_ACCEPT) {
            $scope.item.route_case_id = null;
            $scope.item.release_reason_id = null;
            $scope.item.airp_id = null;
            $scope.item.calling_station_id = null;
            $scope.item.called_station_id = null;
            $scope.item.header = null;
        }
        if ($scope.item.type_id == $scope.TYPE_ID_AUTOMATIC_2) {
            $scope.item.route_case_id = null;
            $scope.item.release_reason_id = null;
            $scope.item.airp_id = null;
            $scope.item.calling_station_id = null;
            $scope.item.called_station_id = null;
            $scope.item.header = null;
        }

        Outcome.save($scope.item).then(function (response) {
            $modalInstance.close();
        });
    };

    $scope.stringifyData = function (data) {
        if (!data || data == '{}') {
            return '{}';
        }

        return '{' + data.join(',') + '}';
    };

    $scope.parseData = function (data) {
        if (!data) {
            return [];
        }

        return data.replace('{', '').replace('}', '').split(',');
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };

    $scope.addOutcomeRule = function () {
        $scope.item.outcomeRules.push({
            trunk_group_id: '',
            server_id: $scope.server.id,
            allow: $scope.item.outcome_rule_default_allowed
        });
    };

    $scope.removeOutcomeRule = function (index) {
        $scope.item.outcomeRules.splice(index, 1);
    };

    $scope.clickRouteTableItem = function (item) {
        if (window.getSelection().type == 'Range') return;

        Redirect.routeTableEdit(item.id).then(function () {
            $scope.init();
        });
    };
};