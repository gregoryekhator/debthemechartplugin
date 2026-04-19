<?php
/**
 * Path: /var/www/html/moodle_test/local/chartplugin/index.php
 */
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

global $PAGE, $USER, $OUTPUT, $SESSION, $DB, $CFG;

require_login();

$type   = optional_param('type', 'synopsis', PARAM_ALPHANUMEXT);
$format = optional_param('format', 'bar', PARAM_ALPHANUMEXT);

// 1. Subscription & Payment Logic (Fixes Q841 error).
$is_enterprise = false;
$global_override = get_config('local_chartplugin', 'enable_global_enterprise');

// Use record_exists to prevent the "found more than one record" exception.
$has_subscription = $DB->record_exists('local_chartplugin_payments', ['userid' => $USER->id, 'status' => 'completed']);

if ($has_subscription || $global_override) {
    $is_enterprise = true;
}

// 2. Dynamic AI Hero Text (Stage 1).
$ai_text = \local_chartplugin\analytics\synopsis::get_ai_performance_delta($USER->id);
if ($is_enterprise) {
    $ai_text = "<strong>Neural Link Active:</strong> Systemic performance has increased by 14% since your last recovery session.";
}

// 3. Page Setup.
$PAGE->set_url(new moodle_url('/local/chartplugin/index.php', ['type' => $type]));
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('report');
$renderer = $PAGE->get_renderer('local_chartplugin');

// 4. Navigation (Active Button Color logic).
$defs = \local_chartplugin\analytics\synopsis::get_button_definitions();
$nav_items = [];
foreach ($defs as $key => $details) {
    $is_locked = ($key === 'style' || $key === 'best_plan') && !$is_enterprise;
    $nav_items[] = [
        'name'    => $details['label'],
        'url'     => $is_locked ? '#' : new moodle_url('/local/chartplugin/index.php', ['type' => $key, 'format' => $details['default_render']]),
        'active'  => ($type === $key),
        'locked'  => $is_locked,
        'class'   => ($type === $key) ? 'deb-active-now' : 'deb-nav-btn', // Matches your style.css dark blue.
        'tooltip' => $details['tooltip']
    ];
}

// 5. Main Chart.
$chart_data = \local_chartplugin\analytics\synopsis::get_chart_data($USER->id, $type);
$chart_class = "\\core\\chart_" . $format;
$main_chart_obj = new $chart_class();
$main_chart_obj->add_series($chart_data['series']);
$main_chart_obj->set_labels($chart_data['labels']);

// 6. Cockpit History.
if (!isset($SESSION->chart_history)) { $SESSION->chart_history = []; }
if (empty($SESSION->chart_history) || $SESSION->chart_history[0] !== $type) {
    array_unshift($SESSION->chart_history, $type);
    $SESSION->chart_history = array_slice($SESSION->chart_history, 0, 3);
}

$history_blocks = [];
for ($i = 1; $i <= 2; $i++) {
    $h_key = $SESSION->chart_history[$i] ?? null;
    $block = new stdClass();
    $block->title = "Telemetry Pending";
    $block->html  = '<div class="text-center mt-5 text-muted small"><i class="fa fa-refresh fa-spin fa-2x mb-2"></i><br>Syncing...</div>';

    if ($h_key && isset($defs[$h_key])) {
        $h_data = \local_chartplugin\analytics\synopsis::get_chart_data($USER->id, $h_key);
        $h_chart = new \core\chart_line();
        $h_chart->add_series($h_data['series']);
        $h_chart->set_labels($h_data['labels']);
        $block->title = $defs[$h_key]['label'];
        $block->html  = $renderer->render($h_chart);
    }
    $history_blocks[] = $block;
}

// 7. Data Assembly for Template.
$data = [
    'is_enterprise'   => $is_enterprise,
    'ai_hero_text'    => $ai_text,
    'chart_title'     => $defs[$type]['label'],
    'chart_tooltip'   => $defs[$type]['tooltip'],
    'current_type'    => $type,
    'is_bar'          => ($format === 'bar'),
    'is_line'         => ($format === 'line'),
    'is_pie'          => ($format === 'pie'),
    'main_chart_html' => $renderer->render($main_chart_obj),
    'nav_items'       => $nav_items,
    'last_viewed'     => $history_blocks[0],
    'prev_viewed'     => $history_blocks[1],
    // Footer Variable Marriage.
    'brandorganization_footer' => 'Debonair Training Systems',
    'custom_footer_data' => [
        'contact_web'   => 'www.debonair.com',
        'contact_email' => 'support@debonair.com',
        'contact_phone' => '+44 20 7946 0000',
        'copyright'     => '© 2026 Debonair Training Systems'
    ]
];

echo $OUTPUT->header();
echo $renderer->render_from_template('local_chartplugin/custom_header', $data);
echo $renderer->render_from_template('local_chartplugin/analytics_page', $data);
echo $renderer->render_from_template('local_chartplugin/custom_footer', $data);
echo $OUTPUT->footer();