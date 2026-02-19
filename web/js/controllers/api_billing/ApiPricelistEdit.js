var ApiBillingApiPricelistEditCtrl = function (
    $rootScope,
    $scope,
    ApiBillingApiPricelist,
    params,
    $modalInstance,
    $window // используется в exportToExcel
) {
    if (params.id) {
        ApiBillingApiPricelist.get({ id: params.id }).then(function (data) {
            $scope.item = data;
        });
    } else {
        $scope.item = {
            name: '',
            items: []
        };
    }

    $scope.sortableOptions = {
        update: function (e, ui) {
            var sortBlocked = false;

            var dropMin, dropMax;
            if (ui.item.sortable.index < ui.item.sortable.dropindex) {
                dropMin = ui.item.sortable.index;
                dropMax = ui.item.sortable.dropindex;
            } else {
                dropMin = ui.item.sortable.dropindex;
                dropMax = ui.item.sortable.index;
            }

            // Безопасность: routes может не быть у прайс-листа
            if (!$scope.item.routes) {
                return;
            }

            for (var routeKey in $scope.item.routes) {
                if (
                    routeKey >= dropMin &&
                    routeKey <= dropMax &&
                    $scope.item.routes[routeKey]['is_locked']
                ) {
                    sortBlocked = true;
                }
            }

            if (sortBlocked) {
                ui.item.sortable.cancel();
            }
        },
        axis: 'y'
    };

    $scope.save = function () {
        ApiBillingApiPricelist.save($scope.item).then(function () {
            $modalInstance.close();
        }).catch(function (err) {
            console.error('Save error', err);
            var msg = (err && err.data && (err.data.message || err.data.error)) || 'Ошибка сохранения, подробности в консоли.';
            $window.alert(msg);
        });
    };

    $scope.back = function () {
        $modalInstance.dismiss();
    };

    $scope.addPricelistItem = function () {
        $scope.item.items.push({
            pricelist_id: '',
            api_id: '',
            api_method_id: '',
            price: 0,
            cost: 0,
            enabled: true
            // id не задаём — сервер различит новые/старые по наличию id
        });
    };

    $scope.removePricelistItem = function (index) {
        $scope.item.items.splice(index, 1);
    };

    $scope.exportToExcel = function () {
        // ВНИМАНИЕ: сервис должен слать { responseType: 'arraybuffer' }
        ApiBillingApiPricelist.exportToExcel({ id: $scope.item.id }).then(
            function (response) {
                var blob = new Blob([response.data], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                });
                var url = URL.createObjectURL(blob);
                var a = document.createElement('a');
                a.href = url;
                a.download = 'Pricelist_' + $scope.item.name + '.xlsx';
                document.body.appendChild(a);
                a.click();
                URL.revokeObjectURL(url);
                document.body.removeChild(a);
            },
            function (err) {
                console.error('Export error', err);
                $window.alert('Не удалось экспортировать прайс-лист.');
            }
        );
    };
};
