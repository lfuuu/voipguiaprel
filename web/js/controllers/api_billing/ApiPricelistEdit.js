var ApiBillingApiPricelistEditCtrl = function($rootScope, $scope, ApiBillingApiPricelist, params, $modalInstance) {
    if (params.id) {
        ApiBillingApiPricelist.get({id: params.id}).then(function (data) {
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

            if (ui.item.sortable.index < ui.item.sortable.dropindex) {
                var dropMin = ui.item.sortable.index;
                var dropMax = ui.item.sortable.dropindex;
            } else {
                var dropMin = ui.item.sortable.dropindex;
                var dropMax = ui.item.sortable.index;
            }

            for (var routeKey in $scope.item.routes) {
                if (routeKey >= dropMin && routeKey <= dropMax && $scope.item.routes[routeKey]['is_locked']) {
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
            enabled: true
        });
    };

    $scope.removePricelistItem = function (index) {
        $scope.item.items.splice(index, 1);
    };
    $scope.exportToExcel = function() {
        ApiBillingApiPricelist.exportToExcel({ id: $scope.item.id })
            .then(function(response) {
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
            }, function(err) {
                console.error('Export error', err);
                $window.alert('Не удалось экспортировать прайс-лист.');
            });
    };
};
