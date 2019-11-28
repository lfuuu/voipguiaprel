(function () {
    app.directive('selectBox', selectBox);

    selectBox.$inject = ['List', 'Redirect'];

    function selectBox(List, Redirect) {
        var directive = {
            link: link,
            templateUrl: '/templates/directives/select-box.html',
            restrict: 'E',
            scope: {
                model: '=',
                placeholder: '@',
                default: '@',
                required: '@',
                param: '@',
                disabled: '@',
                multiple: '@'
            }
        };
        return directive;

        function link(scope, element, attrs) {
            scope.open = openItem;
            loadList();

            scope.$watch('model', function (newVal, oldVal) {
                if (newVal == 'new') {
                    scope.model = oldVal;
                    openItem(null);
                }
            });

            function loadList() {
                var listFunction = List[attrs.list];
                if (listFunction !== undefined) {
                    listFunction(scope.param).then(function (data) {
                        scope.list = data;
                    })
                }
            }

            function openItem(itemId) {
                var editFunction = Redirect[attrs.list + 'Edit'];
                var clearFunction = List[attrs.list + 'ClearList'];

                if (editFunction !== undefined) {
                    editFunction(itemId, scope.param).then(function () {
                        if (clearFunction !== undefined) {
                            clearFunction(scope.param);
                        }

                        loadList();
                    })
                }
            }
        }
    }
})();