<?php 
defined('MOODLE_INTERNAL') || die(); 

$THEME->name = 'debtheme'; 
$THEME->stylesheets = ['custom'];
$THEME->sheets = []; 
$THEME->editor_sheets = []; 
$THEME->parents = ['boost']; 
$THEME->scss = function($theme) {
    return theme_debtheme_get_main_scss_content($theme);
};

$THEME->enable_dock = false;
$THEME->yuicssmodules = [];
$THEME->csspostprocess = 'theme_debtheme_css_postprocess';

$THEME->layouts = [
    'default' => [
        'file' => 'columns2.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    // Moodle 4.x Dashboard primary layout
    'drawers' => [
        'file' => 'columns2.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'frontpage' => [
        'file' => 'columns2.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'mydashboard' => [
        'file' => 'columns2.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'mypublic' => [
        'file' => 'columns2.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'course' => [
        'file' => 'columns2.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'admin' => [
        'file' => 'columns2.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'report' => [
        'file' => 'columns2.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre',
    ],
    'login' => [
        'file' => 'columns2.php',
        'regions' => [],
    ],
];