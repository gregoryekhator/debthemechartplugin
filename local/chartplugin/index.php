<?php
// /var/www/html/moodle_test/local/chartplugin/index.php

require('../../config.php');
require_login();

// 1. Mandatory Moodle Lifecycle: Set context before any output
$context = context_system::instance();
$PAGE->set_context($context);

$type = optional_param('type', 'synopsis', PARAM_ALPHANUMEXT);
$PAGE->set_url(new moodle_url('/local/chartplugin/index.php', ['type' => $type]));

// 2. Layout & Title
$PAGE->set_pagelayout('report');
$PAGE->set_title('Learning Analytics');

// 3. History Stack Logic
if (!isset($SESSION->history_stack)) {
    $SESSION->history_stack = ['synopsis', 'synopsis', 'synopsis'];
}
if ($type !== $SESSION->history_stack[0]) {
    array_unshift($SESSION->history_stack, $type);
    $SESSION->history_stack = array_slice($SESSION->history_stack, 0, 3);
}

$labels = [
    'synopsis' => 'Synopsis (to date)', 'best' => 'Best Courses', 
    'cohort' => 'Cohort Performance', 'plan' => 'Best Learning Plan',
    '30day' => '30 Day Synopsis', 'lowest' => 'Lowest Courses', 
    'freq' => 'Study Frequency', 'style' => 'Preferred Learning Style'
];

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_chartplugin/analytics_page', [
    'chart_title' => $labels[$type],
    'main_chart' => $OUTPUT->render(\local_chartplugin\analytics\synopsis::build_dynamic_chart($type, false)),
    'ai_hero_text' => "Analysis complete. Performance is stable at 66.67%.",
    'buttons' => array_map(function($k, $v) use ($type) {
        return ['label' => $v, 'url' => new moodle_url('/local/chartplugin/index.php', ['type' => $k]), 'active' => ($type == $k)];
    }, array_keys($labels), $labels),
    'history_blocks' => [
        ['title' => 'Last Chart', 'subtitle' => $labels[$SESSION->history_stack[1]], 'content' => $OUTPUT->render(\local_chartplugin\analytics\synopsis::build_dynamic_chart($SESSION->history_stack[1], true))],
        ['title' => 'Previous Chart', 'subtitle' => $labels[$SESSION->history_stack[2]], 'content' => $OUTPUT->render(\local_chartplugin\analytics\synopsis::build_dynamic_chart($SESSION->history_stack[2], true))]
    ]
]);

echo $OUTPUT->footer();