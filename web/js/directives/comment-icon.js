(function () {
    app.directive('commentIcon', commentIcon);

    commentIcon.$inject = ['Redirect'];

    function commentIcon(Redirect) {
        var directive = {
            link: link,
            templateUrl: '/templates/directives/comment-icon.html',
            restrict: 'E',
            scope: {
                key: '@',
                type: '@',
                comment: '='
            }
        };
        return directive;

        function link(scope) {
            scope.editComment = editComment;

            function editComment() {
                Redirect.commentEdit(scope.key, scope.type, scope.comment).then(function (response) {
                    scope.comment = response;
                });
            }
        }
    }
})();