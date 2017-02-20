var app = angular
    .module('app', ['ui.bootstrap', 'ui.select2', 'ui.sortable'])
    .constant('STAT_HOST', 'https://stat.mcn.ru');

app.run(function($rootScope, $templateCache){
    $rootScope.tabs = [];

    $.each(window.templates, function () {
        $templateCache.put(this, window.templates[this]);
    });

    delete window.templates;
});
