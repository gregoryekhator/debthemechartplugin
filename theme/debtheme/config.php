<?php
defined('MOODLE_INTERNAL') || die();

$THEME->name = 'debtheme';
$THEME->stylesheets = ['custom'];
$THEME->sheets = [];
$THEME->editor_sheets = [];
$THEME->parents = ['boost'];
$THEME->secondarynavigation = true;
$THEME->scss = function($theme) {
    return theme_debtheme_get_main_scss_content($theme);
};

$THEME->enable_dock = false;
$THEME->yuicssmodules = [];
$THEME->csspostprocess = 'theme_debtheme_css_postprocess';

$THEME->layouts = [
    'default'     => ['file' => 'columns2.php', 'regions' => ['side-pre', 'side-post'], 'defaultregion' => 'side-pre'],
    'frontpage'   => ['file' => 'columns2.php', 'regions' => ['side-pre', 'side-post'], 'defaultregion' => 'side-pre'],
    'mydashboard' => ['file' => 'columns2.php', 'regions' => ['side-pre', 'side-post'], 'defaultregion' => 'side-pre'],
    'mypublic'    => ['file' => 'columns2.php', 'regions' => ['side-pre', 'side-post'], 'defaultregion' => 'side-pre'],
    'course'      => ['file' => 'columns2.php', 'regions' => ['side-pre', 'side-post'], 'defaultregion' => 'side-pre'],
    'admin'       => ['file' => 'columns2.php', 'regions' => ['side-pre', 'side-post'], 'defaultregion' => 'side-pre'],
    'login'       => ['file' => 'columns2.php', 'regions' => []],

    // The "Flight Deck" Bridge:
    'report'      => [
        'file' => 'columns2.php',
        'regions' => ['side-pre'],
        'defaultregion' => 'side-pre', // Added to fix image_ab57d9.png warning
        'options' => ['nocourseheader' => true]
    ],
    
];