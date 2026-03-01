<?php
require_once(__DIR__ . '/../../config.php');

// 1. Authentication and Context
require_login();
$type = optional_param('type', 'synopsis', PARAM_ALPHANUMEXT);
$context = context_system::instance();

// 2. Page Setup
$PAGE->set_url(new moodle_url('/local/chartplugin/index.php', ['type' => $type]));
$PAGE->set_context($context);
$PAGE->set_pagelayout('report'); // Triggers columns2.php
$PAGE->set_title("Debonair AI - " . ucwords($type));

// 3. Data Preparation
$data = [
    'chart_title' => ucwords(str_replace('_', ' ', $type)),
    'chart_subtitle' => 'Detailed AI analytics for ' . $type,
    'main_chart' => '<div class="p-5 text-center"><h3>Data for ' . $type . ' incoming...</h3></div>',
    'history_blocks' => []
];

// 4. Output: Hand data to the renderer
$output = $PAGE->get_renderer('local_chartplugin');
$renderable = new \local_chartplugin\output\analytics_page($data);

echo $OUTPUT->header();
echo $output->render($renderable);
echo $OUTPUT->footer();