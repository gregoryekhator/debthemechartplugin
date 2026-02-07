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
    // Default fallback layout.
    'default' => [
        'file' => 'columns2.php',
        'regions' => ['side-pre', 'side-post'],
        'defaultregion' => 'side-pre',
    ],

    // Front page (site root)
    'frontpage' => [
        'file' => 'columns2.php',
        'regions' => ['side-pre', 'side-post'],
        'defaultregion' => 'side-pre',
    ],

    // Dashboard
    'mydashboard' => [
        'file' => 'columns2.php',
        'regions' => ['side-pre', 'side-post'],
        'defaultregion' => 'side-pre',
    ],

    // My courses page
    'mypublic' => [
        'file' => 'columns2.php',
        'regions' => ['side-pre', 'side-post'],
        'defaultregion' => 'side-pre',
    ],

    // Course pages
    'course' => [
        'file' => 'columns2.php',
        'regions' => ['side-pre', 'side-post'],
        'defaultregion' => 'side-pre',
    ],

    // Admin pages
    'admin' => [
        'file' => 'columns2.php',
        'regions' => ['side-pre', 'side-post'],
        'defaultregion' => 'side-pre',
    ],

    // Miscellaneous pages (optional, catches edge cases)
    'popup' => [
        'file' => 'columns2.php',
        'regions' => [],
    ],
    'login' => [
        'file' => 'columns2.php',
        'regions' => [],
    ],
];

