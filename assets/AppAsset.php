<?php
namespace app\assets;

use app\classes\AssetBundle;

class AppAsset extends AssetBundle
{
    public $css = [
        'css/site.css',
    ];

    public $js = [
        'js/app.js',

        'js/services/ApiLoader.js',
        'js/services/Api.js',
        'js/services/Redirect.js',

        'js/directives/select-box.js',
        'js/directives/outcome-select-box.js',

        'js/controllers/Main.js',
        'js/controllers/RoutingReport.js',
        'js/controllers/RouteTableEdit.js',
        'js/controllers/RouteTableList.js',
        'js/controllers/SelectRossvyazOperator.js',
        'js/controllers/RoutingEdit.js',
        'js/controllers/OperatorEdit.js',
        'js/controllers/OperatorList.js',
        'js/controllers/SettingsEdit.js',
        'js/controllers/NumberEdit.js',
        'js/controllers/NumberList.js',
        'js/controllers/OutcomeEdit.js',
        'js/controllers/OutcomeList.js',
        'js/controllers/RouteCaseEdit.js',
        'js/controllers/RouteCaseList.js',
        'js/controllers/ReleaseReasonEdit.js',
        'js/controllers/ReleaseReasonList.js',
        'js/controllers/CpcEdit.js',
        'js/controllers/CpcList.js',
        'js/controllers/AirpEdit.js',
        'js/controllers/AirpList.js',
        'js/controllers/PrefixlistEdit.js',
        'js/controllers/PrefixlistList.js',
        'js/controllers/TrunkEdit.js',
        'js/controllers/TrunkList.js',
        'js/controllers/TrunkGroupEdit.js',
        'js/controllers/TrunkGroupList.js',
    ];

    public $templates = [
        'templates/airp_edit.html',
        'templates/airp_list.html',
        'templates/cpc_edit.html',
        'templates/cpc_list.html',
        'templates/main.html',
        'templates/number_edit.html',
        'templates/number_list.html',
        'templates/operator_edit.html',
        'templates/operator_list.html',
        'templates/outcome_edit.html',
        'templates/outcome_list.html',
        'templates/prefixlist_edit.html',
        'templates/prefixlist_list.html',
        'templates/release_reason_edit.html',
        'templates/release_reason_list.html',
        'templates/route_case_edit.html',
        'templates/route_case_list.html',
        'templates/route_table_edit.html',
        'templates/route_table_list.html',
        'templates/trunk_edit.html',
        'templates/trunk_list.html',
        'templates/trunk_group_edit.html',
        'templates/trunk_group_list.html',
        'templates/routing_report.html',
        'templates/select_rossvyaz_operator.html',
        'templates/settings_edit.html',

        'templates/directives/select-box.html',
        'templates/directives/outcome-select-box.html',
    ];
}
