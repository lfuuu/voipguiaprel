var NodeEditCtrl = function($rootScope, $scope, Node, params, $modalInstance, $window) {
     $scope.netTypeOptions = [
      { value: 'border',   label: 'Пограничный' },
      { value: 'internal', label: 'Внутренний' }
    ];
    Node.nodeTypes().then(function(data) {
        $scope.nodeTypes = data;
    });
    Node.nodeStatuses().then(function(data) {
        $scope.nodeStatuses = data;
    });
    Node.russianDistricts().then(function(data) {
        $scope.russianDistricts = data;
    });
    Node.russianSubjects().then(function(data) {
        $scope.russianSubjects = data;
    });
    Node.russianCities().then(function(data) {
        $scope.russianCities = data;
    });

    Node.serverList().then(function(data) {
        $scope.serverList = data;
    });

    if (params.node_id) {
        // При редактировании берём из бэка и уже будут net_type и ss7_spc
        Node.get({ id: params.node_id }).then(function(data) {
            $scope.item = data;
        });
    } else {
        // При создании инициализируем новые поля
        $scope.item = {
            node_name_id: '',
            node_type_id: '',
            node_status_id: '',
            ipaddress: '',
            address: '',
            russian_district_id: '',
            russian_subject_id: '',
            russian_city_id: '',
            comment: '',
           net_type: '',
           ss7_spc: ''
        };
    }

    $scope.save = function() {
        Node.save($scope.item).then(function() {
            $modalInstance.close();
        });
    };

    $scope.back = function() {
        $modalInstance.dismiss();
    };
};
