<?php
namespace app\assets;

use app\classes\AssetBundle;

class AppCamelAsset extends AssetBundle
{
    public $css = [
        '/css/site.css',
    ];

    public $js = [
        'js/app.js',
        'js/services/ApiLoader.js',
        'js/services/Api.js',
        'js/services/CamelApi.js',
        'js/services/Filter.js',
        'js/services/Redirect.js',
        'js/directives/select-box.js',
        'js/directives/select-box-new.js',
        'js/directives/outcome-select-box.js',
        'js/directives/comment-icon.js',
        'js/directives/action-log-view-button.js',
        'js/directives/camel-select-box.js',
        'js/controllers/MainCamel.js',
        'js/controllers/ActionLogView.js',
        'js/controllers/CommentEdit.js',
        'js/controllers/camel/TrunkEdit.js',
        'js/controllers/camel/TrunkList.js',
        'js/controllers/camel/GtEdit.js',
        'js/controllers/camel/GtList.js',
        'js/controllers/camel/RouteTableEdit.js',
        'js/controllers/camel/RouteTableList.js',
        'js/controllers/camel/TestGroupEdit.js',
        'js/controllers/camel/TestGroupList.js',
        'js/controllers/camel/TestAuthEdit.js',
        'js/controllers/camel/TestAuthList.js',
        'js/controllers/camel/TestAuthShowTest.js',
        'js/controllers/camel/OutcomeEdit.js',
        'js/controllers/camel/OutcomeList.js',
        'js/controllers/camel/SettingsEdit.js',
        'js/controllers/PrefixlistEdit.js',
        'js/controllers/PrefixlistList.js',
        'js/controllers/NumberEdit.js',
        'js/controllers/NumberList.js',
    ];

    public $templates = [
        'templates/directives/select-box.html',
        'templates/directives/select-box-new.html',
        'templates/directives/comment-icon.html',
        'templates/directives/action-log-view-button.html',
        'templates/directives/outcome-select-box.html',
        'templates/directives/select-box.html',
        'templates/main_camel.html',
        'templates/action_log_view.html',
        'templates/camel/trunk_edit.html',
        'templates/camel/trunk_list.html',
        'templates/camel/gt_edit.html',
        'templates/camel/gt_list.html',
        'templates/camel/route_table_edit.html',
        'templates/camel/route_table_list.html',
        'templates/camel/test_group_edit.html',
        'templates/camel/test_group_list.html',
        'templates/camel/test_auth_edit.html',
        'templates/camel/test_auth_list.html',
        'templates/camel/test_auth_show_test.html',
        'templates/camel/outcome_edit.html',
        'templates/camel/outcome_list.html',
        'templates/camel/settings_edit.html',
        'templates/prefixlist_edit.html',
        'templates/prefixlist_list.html',
        'templates/number_edit.html',
        'templates/number_list.html',
    ];
}
