<?php
namespace app\assets;

use app\classes\AssetBundle;

class AppLibAsset extends AssetBundle
{
    public $css = [
        'lib/jquery-ui/css/smoothness/jquery-ui-1.10.4.custom.min.css',
        '/lib/bootstrap/css/bootstrap.min.css',
        //'lib/bootstrap/css/bootstrap-theme.min.css',
        '/lib/select2/select2.css',
        '/lib/ui-tree/angular-ui-tree.css',
    ];

    public $js = [
        'lib/jquery/jquery-2.1.0.min.js',
        'lib/jquery-ui/js/jquery-ui-1.10.4.custom.min.js',
        'lib/bootstrap/js/bootstrap.min.js',
        'lib/angularjs/angular.min.js',
        'lib/ui-bootstrap-tpls-0.10.0.min.js',
        'lib/select2/select2.js',
        'lib/ui-select2/select2.js',
        'lib/ui-sortable/sortable.js',
        'lib/file-api/FileAPI.min.js',
        'lib/file-api/jquery.fileapi.min.js',
        'lib/ui-tree/angular-ui-tree.js'
    ];
}
