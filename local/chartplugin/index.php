<?php
/**
 * Path: /var/www/html/moodle_test/local/chartplugin/index.php
 * Re-assembly: Stable Phase
 */
require_once(__DIR__ . '/../../config.php');

$type = optional_param('type', 'synopsis', PARAM_ALPHANUM);
$render = optional_param('render', 'bar', PARAM_ALPHANUM);

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/chartplugin/index.php'), ['type' => $type, 'render' => $render]);
$PAGE->set_title("AI Analytics Dashboard");

// 1. Log view
\local_chartplugin\analytics\synopsis::save_view_history($USER->id, $type, $render);

// 2. Get Data
$template_data = \local_chartplugin\analytics\synopsis::get_template_data($USER->id, $type, $render);

echo $OUTPUT->header();

$template_data['current_chart_label'] = \local_chartplugin\analytics\synopsis::get_button_definitions()[$type]['label'] ?? 'Analytics';

$render_vars = array_merge($template_data, [
    'main_chart' => $OUTPUT->render(\local_chartplugin\analytics\synopsis::build_dynamic_chart($type, false, $render)),
    'history_blocks' => \local_chartplugin\analytics\synopsis::get_history_blocks($USER->id),
    'recovery_url' => (new moodle_url('/admin/tool/lp/plans.php', ['userid' => $USER->id]))->out(false)
]);

echo $OUTPUT->render_from_template('local_chartplugin/analytics_page', $render_vars);
echo $OUTPUT->footer();