var app = angular
    .module('app', ['ui.bootstrap', 'ui.select2', 'ui.sortable', 'ui.tree', 'ngCookies'])
    .constant('STAT_HOST', 'https://stat.mcn.ru')
    .config(['$compileProvider', function ($compileProvider) {
        $compileProvider.debugInfoEnabled(false);
    }]);

app.run(function($rootScope, $templateCache){
    $rootScope.tabs = [];

    /*
    // Экспериментальное отключение Angular cacheFactory
    $.each(window.templates, function () {
        $templateCache.put(this, window.templates[this]);
    });
    */

    delete window.templates;
});
