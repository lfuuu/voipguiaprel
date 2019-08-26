(function () {
  app.directive('actionLogViewButton', actionLogViewButton);

  actionLogViewButton.$inject = ['Redirect'];

  function actionLogViewButton(Redirect) {
    var directive = {
      link: link,
      templateUrl: '/templates/directives/action-log-view-button.html',
      restrict: 'E',
      scope: {
        key: '@',
        type: '@',
        comment: '='
      }
    };
    return directive;

    function link(scope) {
      scope.viewActionLog = viewActionLog;

      function viewActionLog() {
        Redirect.actionLogView(scope.key, scope.type).then(function (response) {
          //do_nothing
        });
      }
    }
  }
})();