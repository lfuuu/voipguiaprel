<?php
namespace app\assets;

use app\classes\AssetBundle;

class AppSettingsAsset extends AssetBundle
{
    public $css = [
        '/css/site.css',
    ];

    public $js = [
        'js/app.js',
        'js/services/ApiLoader.js',
        'js/services/Api.js',
        'js/services/SettingsApi.js',
        'js/services/Filter.js',
        'js/services/Redirect.js',
        'js/controllers/MainSettings.js',
        'js/controllers/settings/ActionLogList.js',
        'js/controllers/settings/ActionLogItemView.js',
        'js/controllers/settings/AclList.js',
    ];

    public $templates = [
        'templates/main_settings.html',
        'templates/settings/action_log_list.html',
        'templates/settings/action_log_item_view.html',
        'templates/settings/acl_list.html',
    ];
}
