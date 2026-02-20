<?php
/**
 * Path: /var/www/html/moodle_test/local/chartplugin/index.php
 * Sprint Q693 Update
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/chartplugin/lib.php');

$type = optional_param('type', 'synopsis', PARAM_ALPHA);
$render = optional_param('render', 'bar', PARAM_ALPHA);

require_login();
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/chartplugin/index.php'), ['type' => $type, 'render' => $render]);

// 1. Log this view for Phase B History Persistence
\local_chartplugin\analytics\synopsis::save_view_history($USER->id, $type, $render);

// 2. Auth/OAuth2 Check (Preparation for Google Login Test)
$is_oauth = !empty($SESSION->oauth2state); 

$template_data = \local_chartplugin\analytics\synopsis::get_template_data($USER->id, $type, $render);

echo $OUTPUT->header();

$render_vars = array_merge($template_data, [
    'main_chart' => $OUTPUT->render(\local_chartplugin\analytics\synopsis::build_dynamic_chart($type, false, $render)),
    'history_blocks' => \local_chartplugin\analytics\synopsis::get_history_blocks($USER->id),
    // FIXED: Recovery Plan now opens Competency API in new window
    'recovery_url' => (new moodle_url('/admin/tool/lp/plans.php', ['userid' => $USER->id]))->out(false),
    'is_bar' => ($render === 'bar'),
    'is_line' => ($render === 'line'),
    'is_pie' => ($render === 'pie'),
    'bar_url' => (new moodle_url('/local/chartplugin/index.php', ['type' => $type, 'render' => 'bar']))->out(false),
    'line_url' => (new moodle_url('/local/chartplugin/index.php', ['type' => $type, 'render' => 'line']))->out(false),
    'pie_url' => (new moodle_url('/local/chartplugin/index.php', ['type' => $type, 'render' => 'pie']))->out(false),
]);

echo $OUTPUT->render_from_template('local_chartplugin/analytics_page', $render_vars);
echo $OUTPUT->footer();