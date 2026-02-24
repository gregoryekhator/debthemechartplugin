<?php
/**
 * Path: /local/chartplugin/index.php
 */
require_once(__DIR__ . '/../../config.php');
global $PAGE, $USER, $DB, $OUTPUT;

require_login();

$userid = $USER->id;
$type = optional_param('type', 'synopsis', PARAM_ALPHANUMEXT);
$render_type = optional_param('render', 'bar', PARAM_ALPHANUM);
$payment_status = optional_param('status', '', PARAM_ALPHANUM);

$PAGE->set_url(new moodle_url('/local/chartplugin/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title("Debonair Training AI Dashboard");
$PAGE->set_pagelayout('report');

$renderer = $PAGE->get_renderer('local_chartplugin');

// Database Check for payment status.
$has_paid = $DB->record_exists('local_chartplugin_payments', ['userid' => $userid, 'status' => 'completed']);

$ai_hero_text = \local_chartplugin\analytics\synopsis::get_ai_performance_delta($userid);
$is_active_plan = false;

if ($has_paid || $payment_status === 'paid_success') {
    $ai_hero_text = "Success! Your Recovery Plan is now active. Check your email for coaching details.";
    $is_active_plan = true;
    
    if ($payment_status === 'paid_success' && !$has_paid) {
        $record = new \stdClass();
        $record->userid = $userid;
        $record->amount = 49.99;
        $record->status = 'completed';
        $record->timecreated = time();
        $DB->insert_record('local_chartplugin_payments', $record);
    }
}

// Nav Buttons.
$defs = \local_chartplugin\analytics\synopsis::get_button_definitions();
$buttons = [];
foreach ($defs as $key => $opt) {
    $buttons[] = [
        'label' => $opt['label'],
        'url' => new moodle_url($PAGE->url, ['type' => $key, 'render' => $opt['default_render']]),
        'active_class' => ($type == $key ? 'deb-active-now' : '')
    ];
}

// Charts generation.
$chart_data = \local_chartplugin\analytics\synopsis::get_chart_data($userid, $type);
$chart = ($render_type === 'line') ? new core\chart_line() : (($render_type === 'pie') ? new core\chart_pie() : new core\chart_bar());
$chart->add_series($chart_data['series']);
$chart->set_labels($chart_data['labels']);

// Sidebar History.
$last_chart = new core\chart_line();
$last_series = new core\chart_series('Score', [88, 92, 85, 95]);
$last_series->set_color('#dc3545'); 
$last_chart->add_series($last_series);
$last_chart->set_labels(['W1', 'W2', 'W3', 'W4']);

$prev_chart = new core\chart_line();
$prev_series = new core\chart_series('Score', [60, 65, 70, 75]);
$prev_series->set_color('#dc3545');
$prev_chart->add_series($prev_series);
$prev_chart->set_labels(['D1', 'D5', 'D10', 'D15']);

$data = [
    'ai_hero_text' => $ai_hero_text,
    'buttons' => $buttons,
    'main_chart' => $renderer->render($chart),
    'current_chart_label' => ucfirst(str_replace('_', ' ', $type)),
    'recovery_url' => new moodle_url('/local/chartplugin/index.php', ['status' => 'paid_success']),
    'is_active_plan' => $is_active_plan,
    'history_blocks' => [
        ['title' => 'Last View: Best Courses', 'content' => $renderer->render($last_chart)],
        ['title' => 'Previous View: 30 Day Synopsis', 'content' => $renderer->render($prev_chart)]
    ]
];

echo $OUTPUT->header();
echo $renderer->render_analytics_dashboard($data);
echo $OUTPUT->footer();