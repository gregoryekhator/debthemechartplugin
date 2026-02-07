<?php
require(__DIR__ . '/../../config.php');
require_login();

$type = optional_param('type', 'synopsis', PARAM_ALPHANUMEXT);
$PAGE->set_url(new moodle_url('/local/chartplugin/index.php', ['type' => $type]));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('standard');

$renderer = $PAGE->get_renderer('local_chartplugin');

// Logic for your "Last" and "Previous" blocks
$main_chart = \local_chartplugin\analytics\analyst::get_dynamic_chart($type);
$last_chart = \local_chartplugin\analytics\analyst::get_dynamic_chart('30day');
$prev_chart = \local_chartplugin\analytics\analyst::get_dynamic_chart('best');

echo $OUTPUT->header();
echo $renderer->render_analytics_dashboard([
    'buttons' => $button_data, // your existing 8-button array
    'chart_title' => 'Synopsis (to date)',
    'chart_html' => $OUTPUT->render($main_chart),
    'last_chart_label' => '30 Day Synopsis',
    'last_chart_html' => $OUTPUT->render($last_chart),
    'prev_chart_label' => 'Best Courses',
    'prev_chart_html' => $OUTPUT->render($prev_chart),
    'ai_hero_text' => "Analysis for Synopsis (to date): Metrics are trending positively."
]);
echo $OUTPUT->footer();
