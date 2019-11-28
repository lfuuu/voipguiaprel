<?php
namespace app\assets;

use app\classes\AssetBundle;

class AppApiBillingAsset extends AssetBundle
{
    public $css = [
        '/css/site.css',
    ];

    public $js = [
        'js/app.js',
        'js/services/ApiLoader.js',
        'js/services/Api.js',
        'js/services/ApiBillingApi.js',
        'js/services/Filter.js',
        'js/services/Redirect.js',
        'js/directives/select-box.js',
        'js/directives/select-box-new.js',
        'js/directives/outcome-select-box.js',
        'js/directives/comment-icon.js',
        'js/directives/action-log-view-button.js',
        'js/directives/api-billing-select-box.js',
        'js/controllers/MainApiBilling.js',
        'js/controllers/ActionLogView.js',
        'js/controllers/CommentEdit.js',
        'js/controllers/api_billing/ApiEdit.js',
        'js/controllers/api_billing/ApiList.js',
        'js/controllers/api_billing/ApiMethodEdit.js',
        'js/controllers/api_billing/ApiMethodList.js',
        'js/controllers/api_billing/ApiPricelistEdit.js',
        'js/controllers/api_billing/ApiPricelistList.js',
        'js/controllers/api_billing/ApiPricelistItemEdit.js',
        'js/controllers/api_billing/ApiPricelistItemList.js',
    ];

    public $templates = [
        'templates/directives/select-box.html',
        'templates/directives/select-box-new.html',
        'templates/directives/comment-icon.html',
        'templates/directives/action-log-view-button.html',
        'templates/directives/outcome-select-box.html',
        'templates/directives/select-box.html',
        'templates/main_api_billing.html',
        'templates/action_log_view.html',
        'templates/api_billing/api_edit.html',
        'templates/api_billing/api_list.html',
        'templates/api_billing/api_method_edit.html',
        'templates/api_billing/api_method_list.html',
        'templates/api_billing/api_pricelist_edit.html',
        'templates/api_billing/api_pricelist_list.html',
        'templates/api_billing/api_pricelist_item_edit.html',
        'templates/api_billing/api_pricelist_item_list.html',
    ];
}
