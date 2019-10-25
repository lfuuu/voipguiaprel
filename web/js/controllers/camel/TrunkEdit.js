var CamelTrunkEditCtrl = function($rootScope, $scope, Redirect, CamelTrunk, CamelList, Prefixlist, params, $modalInstance) {
    if (params.id) {
        CamelTrunk.get({id: params.id}).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            name: '',
            server_id: $scope.server.id,
            numberPreprocessing: [],
        };
    }

    $scope.trunkNumberPreprocessingType = [
        {'id': 0, 'name': 'Префикс'},
        {'id': 1, 'name': 'Вставка'},
        {'id': 2, 'name': 'Добавление в конец'},
        {'id': 3, 'name': 'Удаление'},
        {'id': 4, 'name': 'Замена'}
    ];

    Prefixlist.listByType({type_id: 14}).then(function (data) {
        $scope.prefixlistList = data;
    });

    CamelList.routeTable({server_id: $scope.server.id}).then(function (data) {
        $scope.routeTableList = data;
    });

    $scope.save = function () {
        CamelTrunk.save($scope.item).then(function () {
            $modalInstance.close();
        });
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

    $scope.back = function () {
        $modalInstance.dismiss();
    };
};
