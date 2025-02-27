var TrunkNodeLinkListCtrl = function($scope, TrunkNodeLink, Redirect, $window) {
    // Инициализируем массив для хранения данных
    $scope.links = [];
    // Параметры сортировки по умолчанию
    $scope.sortType = 'trunk_node_link_id';
    $scope.sortReverse = false;

    // Функция инициализации: загрузка данных с сервера
    $scope.init = function() {
        TrunkNodeLink.read().then(function(data) {
            $scope.links = data;
        });
    };

    $scope.init();

    // Дополнительные методы, если будут добавлены редактирование/удаление и т.д.
    // Пока раздел только для просмотра, поэтому без кнопок редактирования/создания.
};

app.controller('TrunkNodeLinkListCtrl', TrunkNodeLinkListCtrl);
