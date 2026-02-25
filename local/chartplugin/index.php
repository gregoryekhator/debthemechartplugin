<?php
/**
 * Path: /local/chartplugin/index.php
 */
require_once(__DIR__ . '/../../config.php');
global $PAGE, $USER, $DB, $OUTPUT, $SESSION;

require_login();
$userid = $USER->id;
$type = optional_param('type', 'synopsis', PARAM_ALPHANUMEXT);
$render_type = optional_param('render', '', PARAM_ALPHANUM);
$payment_status = optional_param('status', '', PARAM_ALPHANUM);

$PAGE->set_url(new moodle_url('/local/chartplugin/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title("Debonair Training AI Dashboard");
$PAGE->set_pagelayout('report');
$renderer = $PAGE->get_renderer('local_chartplugin');

// a. Subscription & Admin Badge Logic
$is_admin = is_siteadmin();
$has_paid = $DB->record_exists('local_chartplugin_payments', ['userid' => $userid, 'status' => 'completed']);
$user_level = get_user_preferences('local_chartplugin_license', 'freemium', $USER);
if ($is_admin) { $user_level = 'enterprise'; }

// b. Recovery Plan Logic
$is_active_plan = ($has_paid || $payment_status === 'paid_success' || $is_admin);
if ($payment_status === 'paid_success' && !$has_paid) {
    $record = (object)['userid' => $userid, 'amount' => 49.99, 'status' => 'completed', 'timecreated' => time()];
    $DB->insert_record('local_chartplugin_payments', $record);
    set_user_preference('local_chartplugin_license', 'premium', $userid);
    $user_level = 'premium';
}

// e. History Tracking (3-slot rotation)
if (!isset($SESSION->deb_history)) { $SESSION->deb_history = []; }
if (empty($SESSION->deb_history) || $SESSION->deb_history[0] !== $type) {
    array_unshift($SESSION->deb_history, $type);
    $SESSION->deb_history = array_slice($SESSION->deb_history, 0, 3);
}

// d. Navigation & Definitions
$defs = \local_chartplugin\analytics\synopsis::get_button_definitions();
$current_def = $defs[$type] ?? $defs['synopsis'];
$render_type = $render_type ?: $current_def['default_render'];

$buttons = [];
foreach ($defs as $key => $opt) {
    $buttons[] = [
        'label' => $opt['label'],
        'url' => new moodle_url($PAGE->url, ['type' => $key]),
        'active_class' => ($type == $key ? 'deb-active-now' : '')
    ];
}

// Main Chart Data
$chart_data = \local_chartplugin\analytics\synopsis::get_chart_data($userid, $type);
$chart = ($render_type === 'line') ? new core\chart_line() : (($render_type === 'pie') ? new core\chart_pie() : new core\chart_bar());
$chart->add_series($chart_data['series']);
$chart->set_labels($chart_data['labels']);

// Sidebar History Logic
$history_blocks = [];
$h_keys = [1 => 'Last View', 2 => 'Previous View'];
foreach ($h_keys as $idx => $label) {
    $h_type = $SESSION->deb_history[$idx] ?? null;
    if ($h_type && isset($defs[$h_type])) {
        $h_def = $defs[$h_type];
        $h_data = \local_chartplugin\analytics\synopsis::get_chart_data($userid, $h_type);
        $h_chart = new core\chart_line();
        $h_chart->add_series($h_data['series']);
        $h_chart->set_labels($h_data['labels']);
        $history_blocks[] = [
            'title' => $label . ': ' . $h_def['label'],
            'subtitle' => $h_def['tooltip'],
            'content' => $renderer->render($h_chart)
        ];
    }
}

$data = [
    'user_level' => strtoupper($user_level),
    'ai_hero_text' => \local_chartplugin\analytics\synopsis::get_ai_performance_delta($userid),
    'buttons' => $buttons,
    'main_chart' => $renderer->render($chart),
    'chart_title' => $current_def['label'],
    'chart_subtitle' => $current_def['tooltip'],
    'is_active_plan' => $is_active_plan,
    'recovery_url' => new moodle_url($PAGE->url, ['status' => 'paid_success']),
    'history_blocks' => $history_blocks,
    'current_type' => $type,
    'is_bar' => ($render_type === 'bar'),
    'is_line' => ($render_type === 'line'),
    'is_pie' => ($render_type === 'pie')
];

echo $OUTPUT->header();
echo $renderer->render_analytics_dashboard($data);
echo $OUTPUT->footer();