<?php
namespace app\assets;

use app\classes\AssetBundle;

class AppSmsAsset extends AssetBundle
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
        'js/directives/camel-outcome-select-box.js',
        'js/directives/comment-icon.js',
        'js/directives/action-log-view-button.js',
        'js/directives/camel-select-box.js',
        'js/directives/sms-outcome-select-box.js',
        'js/controllers/MainSms.js',
        'js/controllers/sms/SmsTrunkList.js',
        'js/controllers/sms/SmsRouteTableList.js',
        'js/controllers/sms/SmsOutcomeList.js',
        'js/controllers/sms/SmsOutcomeEdit.js',
        'js/controllers/sms/SmsTrunkEdit.js',
        'js/controllers/sms/SmsRouteTableEdit.js',
        'js/controllers/ActionLogView.js',
        'js/controllers/CommentEdit.js',
        'js/controllers/PrefixlistEdit.js',
        'js/controllers/PrefixlistList.js',
        'js/controllers/NumberEdit.js',
        'js/controllers/NumberList.js',
        'js/directives/sms-select-box.js'
    ];

    public $templates = [
        'templates/directives/select-box.html',
        'templates/directives/select-box-new.html',
        'templates/directives/comment-icon.html',
        'templates/directives/action-log-view-button.html',
        'templates/directives/outcome-select-box.html',
        'templates/directives/select-box.html',
        'templates/main_sms.html',
        'templates/sms/sms_trunk_list.html',
        'templates/sms/sms_route_table_list.html',
        'templates/sms/sms_outcome_list.html',
        'templates/sms/sms_outcome_edit.html',
        'templates/sms/sms_route_table_list.html',
        'templates/sms/route_table_edit.html',
        'templates/action_log_view.html',
        'templates/prefixlist_edit.html',
        'templates/prefixlist_list.html',
        'templates/number_edit.html',
        'templates/number_list.html',
    ];
}