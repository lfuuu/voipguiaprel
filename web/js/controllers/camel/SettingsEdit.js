var CamelSettingsEditCtrl = function ($scope, $window, CamelSettings, List, $modalInstance, CamelList,params) {
    $scope.title = 'Общие настройки';
    $scope.old_name = '';

    $scope.MTS_ANTIFRAUD = 1;
    $scope.EPVV_ANTIFRAUD = 2;
    $scope.nnpMode = $scope.MTS_ANTIFRAUD;

    CamelSettings.get({id: $scope.server.id}).then(function (data) {
        $scope.item = data;
        $scope.old_name = data.name;
    });

    List.server().then(function (data) {
        $scope.serverList = data;
    });

    $scope.save = function () {
        CamelSettings.save($scope.item).then(function (response) {
            if ($scope.old_name !== $scope.item.name || $scope.item.default_routing_server_id !== "" + $scope.server.default_routing_server_id) {
                $window.location.reload();
            } else {
                $modalInstance.close();
            }
        });
    };

    $scope.trunkNumberPreprocessingType = [
        {'id': 0, 'name': 'Префикс'},
        {'id': 1, 'name': 'Вставка'},
        {'id': 2, 'name': 'Добавление в конец'},
        {'id': 3, 'name': 'Удаление'},
        {'id': 4, 'name': 'Замена'},
        {'id': 5, 'name': 'Замена если номер'}
    ];

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
