var CormAdapterEditCtrl = function($scope, TelemetryReceiver, params, $modalInstance) {
    if (params.id) {
        TelemetryReceiver.get({ id: params.id }).then(function(response) {
            $scope.item = response;

            if ($scope.item.address) {
                $scope.item.address = $scope.item.address.map(function(adress) {
                    adress.showComment = false;
                    return adress;
                });
            } else {
                $scope.item.address = [];
            }

        }, function(error) {
            console.error("Ошибка при загрузке адаптера:", error);
        });
    } else {
        $scope.item = {
            name: '',
            address: [],
            sw_shared: false
        };
    }

    $scope.addAddress = function() {
        if (!$scope.item.address) {
            $scope.item.address = [];
        }
        $scope.item.address.push({
            address: '',
            object_comment: '',
            showComment: false
        });
    };

    $scope.removeAddress = function(index) {
        if ($scope.item.address && $scope.item.address.length > index) {
            $scope.item.address.splice(index, 1);
        }
    };    

    $scope.save = function() {
        let data = {
            name: $scope.item.name,
            address: $scope.item.address,
            sw_shared: $scope.item.sw_shared,
            server_id: $scope.server.id
        };
    
        if ($scope.item.id) {
            data.id = $scope.item.id;
            TelemetryReceiver.update(data).then(function(response) {
                $modalInstance.close(response.data);
            }).catch(function(error) {
                console.error('Ошибка при обновлении адаптера:', error);
            });
        } else {
            TelemetryReceiver.save(data).then(function(response) {
                $modalInstance.close(response.data);
            }).catch(function(error) {
                console.error('Ошибка при создании адаптера:', error);
            });
        }
    };
    
    $scope.back = function() {
        $modalInstance.dismiss();
    };
};
