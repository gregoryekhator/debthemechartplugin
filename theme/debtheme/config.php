<?php
defined('MOODLE_INTERNAL') || die();

$THEME->name = 'debtheme';
$THEME->parents = ['boost'];
$THEME->sheets = ['custom'];

// Define the layout once to keep it clean
$deb_layout = [
    'file' => 'columns2.php',
    'regions' => ['side-pre'],
    'defaultregion' => 'side-pre',
];

$THEME->layouts = [
    'default'     => $deb_layout,
    'standard'    => $deb_layout, // Fallback for plugins
    'incourse'    => $deb_layout, // Internal course pages
    'drawers'     => $deb_layout, // Dashboard
    'mydashboard' => $deb_layout,
    'report'      => $deb_layout, // YOUR CHART PLUGIN LAYOUT
    'frontpage'   => $deb_layout,
    'admin'       => $deb_layout,
    'login'       => ['file' => 'columns2.php', 'regions' => []],
];

$THEME->iconsystem = \core\output\icon_system::FONTAWESOME;