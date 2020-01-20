<?php
namespace app\assets;

use app\classes\AssetBundle;

class AppRoutingAsset extends AssetBundle
{
    public $css = [

    ];

    public $js = [
        'js/services/RoutingApi.js',
        'js/controllers/routing/TestDialList.js',
        'js/controllers/routing/TestDialEdit.js',
    ];

    public $templates = [
        'templates/routing/test_dial_edit.html',
        'templates/routing/test_dial_list.html',
    ];
}
