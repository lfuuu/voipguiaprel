(function () {
    app.directive('camelSelectBox', camelSelectBox);

    camelSelectBox.$inject = ['CamelList', 'Redirect'];

    function camelSelectBox(CamelList, Redirect) {
        var directive = {
            link: link,
            templateUrl: '/templates/directives/camel-select-box.html',
            restrict: 'E',
            scope: {
                model: '=',
                placeholder: '@',
                default: '@',
                required: '@',
                param: '@',
                disabled: '@'
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
                var listFunction = CamelList[attrs.list];

                if (listFunction !== undefined) {
                    listFunction({}).then(function (data) {
                        scope.list = data;
                    })
                }
            }

            function openItem(itemId) {
                var editFunction = Redirect['camel' + attrs.list[0].toUpperCase() + attrs.list.slice(1) + 'Edit'];

                if (editFunction !== undefined) {
                    editFunction(itemId).then(function () {
                        loadList();
                    })
                }
            }
        }
    }
})();