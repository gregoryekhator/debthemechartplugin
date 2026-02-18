<?php
/**
 * Path: /var/www/html/moodle_test/local/chartplugin/index.php
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/chartplugin/lib.php');

$type = optional_param('type', 'synopsis', PARAM_ALPHA);
$render = optional_param('render', 'bar', PARAM_ALPHA);

require_login();
$context = context_system::instance();
$PAGE->set_context($context);

$baseurl = new moodle_url('/local/chartplugin/index.php');
$PAGE->set_url($baseurl, ['type' => $type, 'render' => $render]);
$PAGE->set_title('Learning Flight Deck');

// 1. Get unified data from our Logic Class
$template_data = \local_chartplugin\analytics\synopsis::get_template_data($USER->id, $type, $render);

// 2. Build Sidebar History (The Purple Lines)
$history_blocks = [
    ['title' => 'Trend Analysis', 'content' => $OUTPUT->render(\local_chartplugin\analytics\synopsis::build_dynamic_chart('30day', true))],
    ['title' => 'Cohort Stats', 'content' => $OUTPUT->render(\local_chartplugin\analytics\synopsis::build_dynamic_chart('cohort', true))]
];

// 3. Render
echo $OUTPUT->header();

$render_vars = array_merge($template_data, [
    'main_chart' => $OUTPUT->render(\local_chartplugin\analytics\synopsis::build_dynamic_chart($type, false, $render)),
    'history_blocks' => $history_blocks,
    'recovery_url' => (new moodle_url('/my/courses.php'))->out(false),
    'bar_url' => (new moodle_url('/local/chartplugin/index.php', ['type' => $type, 'render' => 'bar']))->out(false),
    'line_url' => (new moodle_url('/local/chartplugin/index.php', ['type' => $type, 'render' => 'line']))->out(false),
    'pie_url' => (new moodle_url('/local/chartplugin/index.php', ['type' => $type, 'render' => 'pie']))->out(false),
]);

echo $OUTPUT->render_from_template('local_chartplugin/analytics_page', $render_vars);
echo $OUTPUT->footer();